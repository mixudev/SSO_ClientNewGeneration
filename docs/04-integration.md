# Integration

## Login

Link the user to the package route:

```blade
<a href="{{ route('ssoclient.redirect') }}">Sign in with SSO</a>
```

The package creates state, PKCE, and nonce, then redirects to the provider discovery authorization endpoint.

## Identity resolution

Bind `IdentityResolver` in the consuming application. The resolver receives a `TokenSet` only after ID Token verification. It must identify the local user by `(issuer, sub)` and apply the application's provisioning/linking policy.

Do not create a local session from `pending_tokens` without resolving and authenticating the user.

## Logout

```blade
<form method="POST" action="{{ route('ssoclient.logout') }}">
    @csrf
    <button type="submit">Sign out</button>
</form>
```

Logout revokes the access token and refresh token when the provider exposes a revocation endpoint, clears local package session state, and redirects to the provider end-session endpoint when available. Local logout remains failure-safe: provider failure still clears the local session and redirects locally.

## Provider contract

The provider must expose OIDC discovery with authorization endpoint, token endpoint, JWKS URI, issuer, and RS256 support. The client redirect URI must be registered exactly.
