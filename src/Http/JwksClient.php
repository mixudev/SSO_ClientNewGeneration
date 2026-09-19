<?php

declare(strict_types=1);

namespace MixuDev\LaravelSsoClient\Http;

use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Http\Client\Factory as HttpFactory;
use MixuDev\LaravelSsoClient\Data\DiscoveryDocument;
use MixuDev\LaravelSsoClient\Exceptions\ProtocolException;

final class JwksClient
{
    public function __construct(private readonly HttpFactory $http, private readonly CacheRepository $cache)
    {
    }

    /** @return array<string, mixed> */
    public function keys(DiscoveryDocument $discovery, bool $fresh = false): array
    {
        $key = 'ssoclient.jwks.'.hash('sha256', $discovery->jwksUri());
        if ($fresh) {
            $this->cache->forget($key);
        }
        $keys = $this->cache->remember($key, (int) config('ssoclient.jwks_cache_ttl', 3600), function () use ($discovery): array {
            $response = $this->http->timeout((int) config('ssoclient.http.timeout', 10))
                ->connectTimeout((int) config('ssoclient.http.connect_timeout', 3))
                ->get($discovery->jwksUri());
            $json = $response->json();
            if ($response->failed() || ! is_array($json) || ! is_array($json['keys'] ?? null)) {
                throw new ProtocolException('SSO JWKS request failed.');
            }

            return $json;
        });

        return $keys;
    }
}
