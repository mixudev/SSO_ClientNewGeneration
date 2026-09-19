<?php

declare(strict_types=1);

namespace MixuDev\LaravelSsoClient\Tests\Feature;

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use MixuDev\LaravelSsoClient\Contracts\IdentityResolver;
use MixuDev\LaravelSsoClient\Data\TokenSet;
use MixuDev\LaravelSsoClient\Tests\TestCase;

final class SsoClientFlowTest extends TestCase
{
    public function test_redirect_contains_state_nonce_and_s256_pkce(): void
    {
        Http::fake(['https://sso.example.test/.well-known/openid-configuration' => Http::response($this->discovery())]);

        $response = $this->get(route('ssoclient.redirect'));
        parse_str((string) parse_url((string) $response->headers->get('Location'), PHP_URL_QUERY), $query);

        $response->assertRedirect();
        self::assertSame('code', $query['response_type']);
        self::assertSame('S256', $query['code_challenge_method']);
        self::assertNotEmpty($query['state']);
        self::assertNotEmpty($query['nonce']);
        self::assertNotEmpty($query['code_challenge']);
        Http::assertSent(fn (Request $request): bool => str_ends_with($request->url(), '/.well-known/openid-configuration'));
    }

    public function test_callback_rejects_missing_state_before_token_exchange(): void
    {
        Http::fake();

        $this->get(route('ssoclient.callback', ['code' => 'code-only']))
            ->assertStatus(500);
        Http::assertNothingSent();
    }

    public function test_refresh_rotates_tokens_and_persists_the_new_set(): void
    {
        $store = app(\MixuDev\LaravelSsoClient\Contracts\TokenStore::class);
        $store->put(new TokenSet('old-access', 'Bearer', 10, 'old-refresh', null));
        Http::fake(['https://sso.example.test/oauth/token' => Http::response([
            'access_token' => 'new-access',
            'refresh_token' => 'new-refresh',
            'token_type' => 'Bearer',
            'expires_in' => 3600,
        ])]);

        $tokens = app(\MixuDev\LaravelSsoClient\Http\TokenClient::class)->refresh(
            new \MixuDev\LaravelSsoClient\Data\DiscoveryDocument($this->discovery()),
        );

        self::assertSame('new-access', $tokens->accessToken);
        self::assertSame('new-refresh', $store->get()?->refreshToken);
    }

    public function test_logout_clears_authenticated_session_and_encrypted_tokens(): void
    {
        $user = new class implements \Illuminate\Contracts\Auth\Authenticatable {
            public function getAuthIdentifierName(): string { return 'id'; }
            public function getAuthIdentifier(): string { return 'user-1'; }
            public function getAuthPasswordName(): string { return 'password'; }
            public function getAuthPassword(): string { return ''; }
            public function getRememberToken(): string { return ''; }
            public function setRememberToken($value): void {}
            public function getRememberTokenName(): string { return ''; }
        };
        app('auth')->login($user);
        app(\MixuDev\LaravelSsoClient\Contracts\TokenStore::class)->put(new TokenSet('access', 'Bearer', 3600, 'refresh', null));

        $this->post(route('ssoclient.logout'))->assertRedirect('/');
        self::assertFalse(auth()->check());
        self::assertNull(app(\MixuDev\LaravelSsoClient\Contracts\TokenStore::class)->get());
    }

    /** @return array<string, mixed> */
    private function discovery(): array
    {
        return [
            'issuer' => 'https://sso.example.test',
            'authorization_endpoint' => 'https://sso.example.test/oauth/authorize',
            'token_endpoint' => 'https://sso.example.test/oauth/token',
            'jwks_uri' => 'https://sso.example.test/.well-known/jwks.json',
            'userinfo_endpoint' => 'https://sso.example.test/oauth/userinfo',
            'end_session_endpoint' => 'https://sso.example.test/oauth/end-session',
        ];
    }
}
