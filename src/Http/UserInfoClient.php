<?php

declare(strict_types=1);

namespace MixuDev\LaravelSsoClient\Http;

use Illuminate\Http\Client\Factory as HttpFactory;
use MixuDev\LaravelSsoClient\Data\DiscoveryDocument;
use MixuDev\LaravelSsoClient\Exceptions\ProtocolException;

final class UserInfoClient
{
    public function __construct(private readonly HttpFactory $http)
    {
    }

    /** @return array<string, mixed> */
    public function get(DiscoveryDocument $discovery, string $accessToken): array
    {
        $endpoint = $discovery->userinfoEndpoint();
        if ($endpoint === null) {
            throw new ProtocolException('SSO UserInfo endpoint is unavailable.');
        }
        $response = $this->http->timeout((int) config('ssoclient.http.timeout', 10))
            ->connectTimeout((int) config('ssoclient.http.connect_timeout', 3))
            ->withToken($accessToken)
            ->get($endpoint);
        $json = $response->json();
        if ($response->failed() || ! is_array($json)) {
            throw new ProtocolException('SSO UserInfo request failed.');
        }

        return $json;
    }
}
