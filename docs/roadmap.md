# Roadmap

## Completed foundation

- Package metadata and Laravel auto-discovery provider.
- Publishable config and routes.
- HTTPS configuration validation.
- Discovery fetch/cache and issuer validation.
- State hash and one-time session consumption.
- PKCE S256 generation.
- Authorization-code token exchange.
- JWKS fetch/cache.
- RS256 ID Token verification with issuer, audience, expiry, and nonce checks.
- Secret-safe `.gitignore` and documentation.

## Next implementation slices

1. Add PHPUnit/Testbench test suite and generated RSA fixtures.
2. Add IdentityResolver callback and local session integration.
3. Add unknown-`kid` one-time JWKS refresh.
4. Add UserInfo client with bearer handling and redaction. (Client class exists; integration tests remain.)
5. Add encrypted TokenStore and refresh single-flight lock.
6. Add provider logout and revocation client. (HTTP clients exist; controller integration remains.)
7. Add default application identity resolver/provisioning policy.
8. Add safe provider error mapper and correlation IDs.
9. Add Laravel 12/13 matrix testing.
10. Run external HTTPS staging smoke test against Mixu SSO.
11. Review dependency/license/security advisories.
12. Tag pre-release, then stable release only after all release gates pass.

## Deferred

- Native/mobile client support.
- SAML.
- Device flow.
- Automatic email-only account linking.
