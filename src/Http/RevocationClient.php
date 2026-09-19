<?php

declare(strict_types=1);

namespace MixuDev\LaravelSsoClient\Http;

use Illuminate\Http\Client\Factory as HttpFactory;
use MixuDev\LaravelSsoClient\Data\DiscoveryDocument;
use MixuDev\LaravelSsoClient\Exceptions\ProtocolException;
use MixuDev\LaravelSsoClient\Support\SsoClientConfig;

final class RevocationClient
{
    public function __construct(
        private readonly HttpFactory $http,
        private readonly SsoClientConfig $config,
    ) {
    }

    public function revoke(DiscoveryDocument $discovery, string $token): void
    {
        $endpoint = $discovery->raw['revocation_endpoint'] ?? null;
        if (! is_string($endpoint) || $endpoint === '') {
            throw new ProtocolException('SSO revocation endpoint is unavailable.');
        }

        $fields = [
            'token' => $token,
            'client_id' => $this->config->clientId(),
        ];
        if (($secret = $this->config->clientSecret()) !== null) {
            $fields['client_secret'] = $secret;
        }
        $response = $this->http->asForm()
            ->timeout((int) config('ssoclient.http.timeout', 10))
            ->connectTimeout((int) config('ssoclient.http.connect_timeout', 3))
            ->post($endpoint, $fields);
        if ($response->failed()) {
            throw new ProtocolException('SSO token revocation failed.');
        }
    }
}
