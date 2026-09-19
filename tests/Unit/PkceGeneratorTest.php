<?php

declare(strict_types=1);

namespace MixuDev\LaravelSsoClient\Tests\Unit;

use MixuDev\LaravelSsoClient\Security\PkceGenerator;
use PHPUnit\Framework\TestCase;

final class PkceGeneratorTest extends TestCase
{
    public function test_pkce_challenge_is_s256_of_verifier(): void
    {
        $result = (new PkceGenerator())->generate();
        $expected = rtrim(strtr(base64_encode(hash('sha256', $result['verifier'], true)), '+/', '-_'), '=');

        self::assertSame($expected, $result['challenge']);
        self::assertGreaterThanOrEqual(43, strlen($result['verifier']));
    }
}
