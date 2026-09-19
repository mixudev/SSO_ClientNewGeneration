<?php

declare(strict_types=1);

namespace MixuDev\LaravelSsoClient\Http;

use Illuminate\Http\Client\Factory as HttpFactory;
use MixuDev\LaravelSsoClient\Contracts\TokenStore;
use MixuDev\LaravelSsoClient\Data\DiscoveryDocument;
use MixuDev\LaravelSsoClient\Data\TokenSet;
use MixuDev\LaravelSsoClient\Exceptions\ProtocolException;
use MixuDev\LaravelSsoClient\Support\SsoClientConfig;

final class TokenClient
{
    public function __construct(
        private readonly HttpFactory $http,
        private readonly SsoClientConfig $config,
        private readonly TokenStore $store,
    ) {
    }

    public function exchange(DiscoveryDocument $discovery, string $code, string $verifier): TokenSet
    {
        return $this->request($discovery, [
            'grant_type' => 'authorization_code',
            'client_id' => $this->config->clientId(),
            'redirect_uri' => $this->config->redirectUri(),
            'code' => $code,
            'code_verifier' => $verifier,
        ]);
    }

    public function refresh(DiscoveryDocument $discovery): TokenSet
    {
        $current = $this->store->get();
        if ($current?->refreshToken === null) {
            throw new ProtocolException('No SSO refresh token is available.');
        }
        $lock = app('cache')->lock('ssoclient.refresh.'.hash('sha256', $current->refreshToken), 30);
        if (! $lock->get()) {
            throw new ProtocolException('SSO token refresh is already in progress.');
        }
        try {
            $latest = $this->store->get();
            if ($latest?->refreshToken !== null && $latest->refreshToken !== $current->refreshToken) {
                return $latest;
            }
            $tokens = $this->request($discovery, [
                'grant_type' => 'refresh_token',
                'client_id' => $this->config->clientId(),
                'refresh_token' => $current->refreshToken,
            ]);
            $this->store->put($tokens);

            return $tokens;
        } finally {
            $lock->release();
        }
    }

    /** @param array<string, string> $fields */
    private function request(DiscoveryDocument $discovery, array $fields): TokenSet
    {
        if (($secret = $this->config->clientSecret()) !== null) {
            $fields['client_secret'] = $secret;
        }
        $response = $this->http->asForm()->timeout((int) config('ssoclient.http.timeout', 10))
            ->connectTimeout((int) config('ssoclient.http.connect_timeout', 3))
            ->post($discovery->tokenEndpoint(), $fields);
        $json = $response->json();
        if ($response->failed() || ! is_array($json) || ! is_string($json['access_token'] ?? null)) {
            throw new ProtocolException('SSO token request failed.');
        }

        return new TokenSet(
            accessToken: $json['access_token'],
            tokenType: is_string($json['token_type'] ?? null) ? $json['token_type'] : 'Bearer',
            expiresIn: is_int($json['expires_in'] ?? null) ? $json['expires_in'] : 0,
            refreshToken: is_string($json['refresh_token'] ?? null) ? $json['refresh_token'] : null,
            idToken: is_string($json['id_token'] ?? null) ? $json['id_token'] : null,
        );
    }
}
