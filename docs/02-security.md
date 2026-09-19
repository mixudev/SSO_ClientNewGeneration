# Security Contract

## Required controls

- HTTPS issuer and callback in production.
- Discovery issuer exact match.
- Config-only issuer; never accept metadata endpoint from request input.
- State is high entropy, hashed, one-time, session-bound, and expiring.
- PKCE verifier is high entropy and never logged.
- OIDC nonce is bound to the authorization transaction.
- ID Token accepts only `RS256`.
- Signature is verified with a JWKS RSA key before claims are trusted.
- Issuer, audience, expiry, and nonce are mandatory checks.
- Network failures fail closed.
- Local account linking is application-controlled.
- Tokens remain server-side for confidential clients.
- Session ID must be regenerated after successful local login.

## Threat cases

The test suite must cover missing/mismatched/expired/replayed state, wrong verifier, wrong issuer, wrong audience, expired token, wrong nonce, forged signature, `alg=none`, unknown `kid`, open redirect, provider timeout, and secret/log leakage.

## Known implementation gaps

- The current foundation does not yet persist encrypted token sets or coordinate refresh-token single-flight.
- Provider logout/revocation clients exist as transport boundaries but are not yet wired into the default controller lifecycle.
- Full Testbench HTTP and external HTTPS interoperability coverage remains required before production release.

The JWKS client performs one bounded cache refresh when an unknown `kid` is encountered.
