<?php

declare(strict_types=1);

namespace MixuDev\LaravelSsoClient\Http;

use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Http\Client\Factory as HttpFactory;
use MixuDev\LaravelSsoClient\Data\DiscoveryDocument;
use MixuDev\LaravelSsoClient\Exceptions\ProtocolException;
use MixuDev\LaravelSsoClient\Support\SsoClientConfig;

final class DiscoveryClient
{
    public function __construct(
        private readonly HttpFactory $http,
        private readonly CacheRepository $cache,
        private readonly SsoClientConfig $config,
    ) {
    }

    public function get(): DiscoveryDocument
    {
        $issuer = $this->config->issuer();
        $key = 'ssoclient.discovery.'.hash('sha256', $issuer);
        $raw = $this->cache->remember($key, (int) config('ssoclient.discovery_cache_ttl', 3600), function () use ($issuer): array {
            $response = $this->http->timeout((int) config('ssoclient.http.timeout', 10))
                ->connectTimeout((int) config('ssoclient.http.connect_timeout', 3))
                ->retry((int) config('ssoclient.http.retry_times', 2), (int) config('ssoclient.http.retry_sleep_ms', 200))
                ->get($issuer.'/.well-known/openid-configuration');
            if ($response->failed() || ! is_array($response->json())) {
                throw new ProtocolException('SSO discovery request failed.');
            }

            return $response->json();
        });
        $document = new DiscoveryDocument($raw);
        if (! hash_equals($issuer, rtrim($document->issuer(), '/'))
            || $document->authorizationEndpoint() === ''
            || $document->tokenEndpoint() === ''
            || $document->jwksUri() === ''
        ) {
            throw new ProtocolException('SSO discovery document is invalid.');
        }

        return $document;
    }
}
