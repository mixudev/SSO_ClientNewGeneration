<?php

declare(strict_types=1);

namespace MixuDev\LaravelSsoClient\Security;

final class PkceGenerator
{
    /** @return array{verifier: string, challenge: string} */
    public function generate(): array
    {
        $verifier = rtrim(strtr(base64_encode(random_bytes(64)), '+/', '-_'), '=');
        $challenge = rtrim(strtr(base64_encode(hash('sha256', $verifier, true)), '+/', '-_'), '=');

        return ['verifier' => $verifier, 'challenge' => $challenge];
    }
}
