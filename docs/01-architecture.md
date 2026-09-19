# Architecture

## Boundaries

```text
Laravel application
  -> SsoClientController
  -> DiscoveryClient / TokenClient / JwksClient
  -> StateManager / PkceGenerator / IdTokenVerifier
  -> application-owned IdentityResolver and TokenStore
```

The package is a relying-party client. It does not own the application's user table, roles, organization rules, or authentication session policy.

## Vendor policy

- `laravel/passport`: not required; it belongs to the Mixu SSO provider.
- Laravel HTTP Client: transport.
- PHP OpenSSL: RS256 signature verification.
- Application: local identity provisioning and session creation.

## Extension points

- `IdentityResolver` converts verified claims into an application user.
- `TokenStore` stores server-side token sets for confidential clients.
- Future audit interface must receive redacted metadata only.

Controllers must remain thin. Cryptographic verification must happen before local authentication.
