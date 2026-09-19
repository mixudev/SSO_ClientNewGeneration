<?php

declare(strict_types=1);

namespace MixuDev\LaravelSsoClient\Services;

use MixuDev\LaravelSsoClient\Data\DiscoveryDocument;
use MixuDev\LaravelSsoClient\Exceptions\ProtocolException;
use MixuDev\LaravelSsoClient\Support\SsoClientConfig;

final readonly class LogoutUrlBuilder
{
    public function __construct(private SsoClientConfig $config)
    {
    }

    public function build(DiscoveryDocument $discovery, ?string $idTokenHint, string $state): string
    {
        $endpoint = $discovery->endSessionEndpoint();
        if ($endpoint === null) {
            throw new ProtocolException('SSO end-session endpoint is unavailable.');
        }

        return $endpoint.'?'.http_build_query(array_filter([
            'id_token_hint' => $idTokenHint,
            'client_id' => $this->config->clientId(),
            'post_logout_redirect_uri' => $this->config->postLogoutRedirectUri(),
            'state' => $state,
        ], static fn (mixed $value): bool => is_string($value) && $value !== ''), '', '&', PHP_QUERY_RFC3986);
    }
}
