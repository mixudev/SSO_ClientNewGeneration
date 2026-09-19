<?php

declare(strict_types=1);

namespace MixuDev\LaravelSsoClient\Contracts;

use MixuDev\LaravelSsoClient\Data\TokenSet;

interface TokenStore
{
    public function put(TokenSet $tokens): void;

    public function get(): ?TokenSet;

    public function forget(): void;
}
