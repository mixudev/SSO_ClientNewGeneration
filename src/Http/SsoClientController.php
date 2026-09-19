<?php

declare(strict_types=1);

namespace MixuDev\LaravelSsoClient\Http;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use MixuDev\LaravelSsoClient\Contracts\IdentityResolver;
use MixuDev\LaravelSsoClient\Data\TokenSet;
use MixuDev\LaravelSsoClient\Contracts\TokenStore;
use MixuDev\LaravelSsoClient\Exceptions\ConfigurationException;
use MixuDev\LaravelSsoClient\Exceptions\ProtocolException;
use MixuDev\LaravelSsoClient\Security\IdTokenVerifier;
use MixuDev\LaravelSsoClient\Security\PkceGenerator;
use MixuDev\LaravelSsoClient\Security\StateManager;
use MixuDev\LaravelSsoClient\Support\SsoClientConfig;

final class SsoClientController
{
    public function __construct(
        private readonly DiscoveryClient $discovery,
        private readonly PkceGenerator $pkce,
        private readonly StateManager $state,
        private readonly SsoClientConfig $config,
        private readonly TokenClient $tokens,
        private readonly IdTokenVerifier $idTokens,
    ) {
    }

    public function redirect(): RedirectResponse
    {
        $document = $this->discovery->get();
        $pkce = $this->pkce->generate();
        $nonce = rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');
        $state = rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');
        $this->state->put($state, [
            'verifier' => $pkce['verifier'],
            'nonce' => $nonce,
            'redirect_uri' => $this->config->redirectUri(),
        ], (int) config('ssoclient.state_ttl', 600));

        return redirect()->away($document->authorizationEndpoint().'?'.http_build_query([
            'client_id' => $this->config->clientId(),
            'redirect_uri' => $this->config->redirectUri(),
            'response_type' => 'code',
            'scope' => implode(' ', $this->config->scopes()),
            'state' => $state,
            'code_challenge' => $pkce['challenge'],
            'code_challenge_method' => 'S256',
            'nonce' => $nonce,
        ], '', '&', PHP_QUERY_RFC3986));
    }

    public function callback(Request $request): RedirectResponse
    {
        if ($request->filled('error') || ! is_string($request->query('code')) || $request->query('code') === '' || ! is_string($request->query('state')) || $request->query('state') === '') {
            throw new ProtocolException('SSO authorization callback is invalid.');
        }
        $context = $this->state->consume($request->query('state'));
        $discovery = $this->discovery->get();
        $tokenSet = $this->tokens->exchange(
            $discovery,
            (string) $request->query('code'),
            $context['verifier'],
        );
        if ($tokenSet->idToken === null) {
            throw new ProtocolException('OIDC ID Token is required.');
        }
        $claims = $this->idTokens->verify($tokenSet->idToken, $discovery, $context['nonce']);
        $verifiedTokenSet = new TokenSet(
            $tokenSet->accessToken,
            $tokenSet->tokenType,
            $tokenSet->expiresIn,
            $tokenSet->refreshToken,
            $tokenSet->idToken,
            $claims,
        );

        if (! app()->bound(IdentityResolver::class)) {
            throw new ConfigurationException('An IdentityResolver binding is required before SSO login.');
        }
        Auth::login(app(IdentityResolver::class)->resolve($verifiedTokenSet));
        app(TokenStore::class)->put($verifiedTokenSet);
        $request->session()->regenerate();

        return redirect('/');
    }

    public function logout(Request $request): RedirectResponse
    {
        app(TokenStore::class)->forget();
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        $request->session()->regenerate();

        return redirect('/');
    }
}
