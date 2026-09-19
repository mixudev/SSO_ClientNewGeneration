<?php

declare(strict_types=1);

namespace MixuDev\LaravelSsoClient\Security;

use MixuDev\LaravelSsoClient\Data\DiscoveryDocument;
use MixuDev\LaravelSsoClient\Exceptions\ProtocolException;
use MixuDev\LaravelSsoClient\Http\JwksClient;
use MixuDev\LaravelSsoClient\Support\SsoClientConfig;

final class IdTokenVerifier
{
    public function __construct(
        private readonly JwksClient $jwks,
        private readonly SsoClientConfig $config,
    ) {
    }

    /** @return array<string, mixed> */
    public function verify(string $jwt, DiscoveryDocument $discovery, string $nonce): array
    {
        $parts = explode('.', $jwt);
        if (count($parts) !== 3) {
            throw new ProtocolException('SSO ID Token format is invalid.');
        }
        [$encodedHeader, $encodedPayload, $encodedSignature] = $parts;
        $header = $this->jsonPart($encodedHeader);
        $payload = $this->jsonPart($encodedPayload);
        $signature = $this->decode($encodedSignature);
        if (($header['alg'] ?? null) !== 'RS256' || ! is_string($header['kid'] ?? null) || $signature === '') {
            throw new ProtocolException('SSO ID Token algorithm or key is invalid.');
        }
        $key = $this->findKey($this->jwks->keys($discovery), $header['kid']);
        if ($key === null) {
            $key = $this->findKey($this->jwks->keys($discovery, true), $header['kid']);
        }
        if ($key === null) {
            throw new ProtocolException('SSO ID Token signing key is unavailable.');
        }
        $publicKey = openssl_pkey_get_public($this->rsaPem($key['n'], $key['e']));
        if ($publicKey === false || openssl_verify($encodedHeader.'.'.$encodedPayload, $signature, $publicKey, OPENSSL_ALGO_SHA256) !== 1) {
            throw new ProtocolException('SSO ID Token signature is invalid.');
        }
        $now = time();
        $audience = $payload['aud'] ?? null;
        if (($payload['iss'] ?? null) !== $discovery->issuer()
            || (! is_string($audience) && ! (is_array($audience) && in_array($this->config->clientId(), $audience, true)))
            || (is_string($audience) && $audience !== $this->config->clientId())
            || ($payload['exp'] ?? 0) <= $now - (int) config('ssoclient.clock_skew', 60)
            || ($payload['nonce'] ?? null) !== $nonce
        ) {
            throw new ProtocolException('SSO ID Token claims are invalid.');
        }

        return $payload;
    }

    /** @return array<string, string>|null */
    private function findKey(array $jwks, string $kid): ?array
    {
        foreach ($jwks['keys'] ?? [] as $candidate) {
            if (is_array($candidate) && ($candidate['kid'] ?? null) === $kid && ($candidate['kty'] ?? null) === 'RSA' && ($candidate['alg'] ?? 'RS256') === 'RS256' && is_string($candidate['n'] ?? null) && is_string($candidate['e'] ?? null)) {
                return $candidate;
            }
        }

        return null;
    }

    /** @return array<string, mixed> */
    private function jsonPart(string $part): array
    {
        $decoded = json_decode($this->decode($part), true);
        if (! is_array($decoded)) {
            throw new ProtocolException('SSO ID Token JSON is invalid.');
        }

        return $decoded;
    }

    private function decode(string $value): string
    {
        $decoded = base64_decode(strtr($value, '-_', '+/'), true);

        return $decoded === false ? '' : $decoded;
    }

    private function rsaPem(string $modulus, string $exponent): string
    {
        $modulus = $this->decode($modulus);
        $exponent = $this->decode($exponent);
        $rsa = "\x30".$this->length(2 + strlen($modulus) + 2 + strlen($exponent))."\x02".$this->length(strlen($modulus)).$modulus."\x02".$this->length(strlen($exponent)).$exponent;

        return "-----BEGIN RSA PUBLIC KEY-----\n".chunk_split(base64_encode("\x30".$this->length(strlen($rsa)).$rsa), 64, "\n")."-----END RSA PUBLIC KEY-----\n";
    }

    private function length(int $length): string
    {
        if ($length < 128) {
            return chr($length);
        }

        $bytes = ltrim(pack('N', $length), "\x00");

        return chr(0x80 | strlen($bytes)).$bytes;
    }
}
