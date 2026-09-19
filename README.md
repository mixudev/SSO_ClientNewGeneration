# MixuDev Laravel SSO Client

Laravel package for secure OAuth 2.0/OIDC Authorization Code + PKCE integration with Mixu SSO.

Status: foundation implementation; production release requires the remaining roadmap and external HTTPS interoperability tests.

## Requirements

- PHP 8.3+
- Laravel 12 or 13
- HTTPS in production
- A registered Mixu SSO `confidential_web` client

`laravel/passport` is not required. Passport is the OAuth2 server dependency of Mixu SSO, not the client dependency. The package uses Laravel HTTP Client and PHP OpenSSL for the current RS256 verification boundary.

## Installation

```bash
composer require mixudev/laravel-sso-client
php artisan vendor:publish --tag=ssoclient-config
```

Add values to `.env` without committing them:

```dotenv
SSO_ISSUER=https://sso.example.com
SSO_CLIENT_ID=your-client-id
SSO_CLIENT_SECRET=your-client-secret
SSO_REDIRECT_URI=https://client.example.com/login/sso/callback
SSO_POST_LOGOUT_REDIRECT_URI=https://client.example.com/
```

The package does not display or log `SSO_CLIENT_SECRET`.

## Routes

The package registers:

- `GET /login/sso` — start authorization.
- `GET /login/sso/callback` — validate state, exchange code, and verify ID Token.
- `POST /logout/sso` — clear local SSO session state.

The application must provide its own `IdentityResolver` binding and create the local authenticated session only after verified claims are resolved. Do not treat a pending token set as a logged-in user.

## Current implementation

Available foundation:

- issuer-based OIDC discovery;
- exact discovery issuer validation;
- bounded HTTP timeout/retry;
- state hash storage and one-time consumption;
- PKCE `S256` generation;
- OIDC nonce generation and binding;
- authorization-code token exchange;
- JWKS retrieval and cache;
- RS256 ID Token signature, issuer, audience, expiry, and nonce verification;
- Laravel service provider and config publishing;
- secret-safe package `.gitignore`.

Not yet complete:

- default application identity resolver and provisioning policy;
- encrypted persistent token store and refresh single-flight;
- UserInfo service;
- provider logout endpoint integration;
- revocation service;
- unknown-`kid` forced JWKS refresh;
- full package feature/integration test suite;
- external HTTPS smoke test;
- signed release and Packagist publication.

## Security rules

- Use Authorization Code + PKCE `S256`.
- Use OIDC `nonce` and validate it against the ID Token.
- Do not put access tokens, refresh tokens, codes, verifier, nonce, or secret in logs or URLs.
- Do not auto-link local accounts by email without an explicit verified-email policy.
- Store confidential-client tokens server-side and encrypted.
- Regenerate the local session ID after login.
- Use exact issuer and redirect configuration.
- Do not bypass signature verification on network or JWKS failure.

## Documentation

Full implementation documentation is in `docs/`:

- `docs/01-architecture.md`
- `docs/02-security.md`
- `docs/03-configuration.md`
- `docs/04-integration.md`
- `docs/05-testing.md`
- `docs/06-release.md`
- `docs/roadmap.md`

## Development

```bash
composer install
composer test
composer lint
```

Before publishing:

```bash
composer validate --strict
composer test
composer lint
```

Do not publish until all items marked as required in `docs/06-release.md` are complete.

## License

MIT
