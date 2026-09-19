<?php

declare(strict_types=1);

namespace MixuDev\LaravelSsoClient\Support;

use Illuminate\Contracts\Encryption\Encrypter;
use Illuminate\Contracts\Session\Session;
use MixuDev\LaravelSsoClient\Contracts\TokenStore;
use MixuDev\LaravelSsoClient\Data\TokenSet;

final class SessionTokenStore implements TokenStore
{
    private const KEY = 'ssoclient.tokens';

    public function __construct(
        private readonly Session $session,
        private readonly Encrypter $encrypter,
    ) {
    }

    public function put(TokenSet $tokens): void
    {
        $this->session->put(self::KEY, $this->encrypter->encrypt(serialize($tokens), false));
    }

    public function get(): ?TokenSet
    {
        $value = $this->session->get(self::KEY);
        if (! is_string($value)) {
            return null;
        }
        try {
            $tokens = unserialize($this->encrypter->decrypt($value, false), ['allowed_classes' => [TokenSet::class]]);
        } catch (\Throwable) {
            return null;
        }

        return $tokens instanceof TokenSet ? $tokens : null;
    }

    public function forget(): void
    {
        $this->session->forget(self::KEY);
    }
}
