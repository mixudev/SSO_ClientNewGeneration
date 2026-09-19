<?php

declare(strict_types=1);

namespace MixuDev\LaravelSsoClient\Data;

final readonly class DiscoveryDocument
{
    /** @param array<string, mixed> $raw */
    public function __construct(public array $raw)
    {
    }

    public function issuer(): string
    {
        return (string) ($this->raw['issuer'] ?? '');
    }

    public function authorizationEndpoint(): string
    {
        return (string) ($this->raw['authorization_endpoint'] ?? '');
    }

    public function tokenEndpoint(): string
    {
        return (string) ($this->raw['token_endpoint'] ?? '');
    }

    public function jwksUri(): string
    {
        return (string) ($this->raw['jwks_uri'] ?? '');
    }

    public function userinfoEndpoint(): ?string
    {
        $endpoint = $this->raw['userinfo_endpoint'] ?? null;

        return is_string($endpoint) && $endpoint !== '' ? $endpoint : null;
    }

    public function endSessionEndpoint(): ?string
    {
        $endpoint = $this->raw['end_session_endpoint'] ?? null;

        return is_string($endpoint) && $endpoint !== '' ? $endpoint : null;
    }
}
