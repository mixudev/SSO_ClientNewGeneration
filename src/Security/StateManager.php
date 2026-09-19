<?php

declare(strict_types=1);

namespace MixuDev\LaravelSsoClient\Security;

use Illuminate\Contracts\Session\Session;
use MixuDev\LaravelSsoClient\Exceptions\ProtocolException;

final class StateManager
{
    private const KEY = 'ssoclient.authorization';

    public function __construct(private readonly Session $session)
    {
    }

    /** @param array{verifier: string, nonce: string, redirect_uri: string} $context */
    public function put(string $state, array $context, int $ttl): void
    {
        $this->session->put(self::KEY, [
            'hash' => hash('sha256', $state),
            'context' => $context,
            'expires_at' => time() + $ttl,
        ]);
    }

    /** @return array{verifier: string, nonce: string, redirect_uri: string} */
    public function consume(string $state): array
    {
        $stored = $this->session->pull(self::KEY);
        if (! is_array($stored)
            || ! is_string($stored['hash'] ?? null)
            || ! hash_equals($stored['hash'], hash('sha256', $state))
            || ! is_int($stored['expires_at'] ?? null)
            || $stored['expires_at'] < time()
            || ! is_array($stored['context'] ?? null)
        ) {
            throw new ProtocolException('SSO authorization state is invalid or expired.');
        }

        $context = $stored['context'];
        if (! is_string($context['verifier'] ?? null)
            || ! is_string($context['nonce'] ?? null)
            || ! is_string($context['redirect_uri'] ?? null)
        ) {
            throw new ProtocolException('SSO authorization context is invalid.');
        }

        return $context;
    }
}
