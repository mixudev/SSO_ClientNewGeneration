<?php

declare(strict_types=1);

namespace MixuDev\LaravelSsoClient\Http;

use Illuminate\Http\Client\Factory as HttpFactory;
use MixuDev\LaravelSsoClient\Data\DiscoveryDocument;
use MixuDev\LaravelSsoClient\Data\TokenSet;
use MixuDev\LaravelSsoClient\Exceptions\ProtocolException;
use MixuDev\LaravelSsoClient\Support\SsoClientConfig;

final class TokenClient
{
    public function __construct(private readonly HttpFactory $http, private readonly SsoClientConfig $config)
    {
    }

    public function exchange(DiscoveryDocument $discovery, string $code, string $verifier): TokenSet
    {
        $fields = [
            'grant_type' => 'authorization_code',
            'client_id' => $this->config->clientId(),
            'redirect_uri' => $this->config->redirectUri(),
            'code' => $code,
            'code_verifier' => $verifier,
        ];
        if (($secret = $this->config->clientSecret()) !== null) {
            $fields['client_secret'] = $secret;
        }
        $response = $this->http->asForm()->timeout((int) config('ssoclient.http.timeout', 10))
            ->connectTimeout((int) config('ssoclient.http.connect_timeout', 3))
            ->post($discovery->tokenEndpoint(), $fields);
        $json = $response->json();
        if ($response->failed() || ! is_array($json) || ! is_string($json['access_token'] ?? null)) {
            throw new ProtocolException('SSO token exchange failed.');
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
