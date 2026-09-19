<?php

declare(strict_types=1);

namespace MixuDev\LaravelSsoClient\Data;

final readonly class TokenSet
{
    /** @param array<string, mixed> $claims */
    public function __construct(
        public string $accessToken,
        public string $tokenType,
        public int $expiresIn,
        public ?string $refreshToken,
        public ?string $idToken,
        public array $claims = [],
    ) {
    }
}
