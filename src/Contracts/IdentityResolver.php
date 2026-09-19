<?php

declare(strict_types=1);

namespace MixuDev\LaravelSsoClient\Contracts;

use Illuminate\Contracts\Auth\Authenticatable;
use MixuDev\LaravelSsoClient\Data\TokenSet;

interface IdentityResolver
{
    public function resolve(TokenSet $tokens): Authenticatable;
}
