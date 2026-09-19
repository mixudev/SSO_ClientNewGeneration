# Testing

Required test layers:

## Unit

- PKCE challenge equals `BASE64URL(SHA256(verifier))`.
- State is hashed and consumed once.
- Expired/mismatched state is rejected.
- Invalid production configuration is rejected.
- ID Token rejects malformed JWT, wrong algorithm, wrong key, wrong issuer, wrong audience, expired token, and wrong nonce.

## Feature

- Redirect route creates provider URL with `state`, `nonce`, `code_challenge`, and `S256`.
- Callback rejects missing state and callback replay.
- Token exchange uses form encoding and never exposes client secret in response.
- Logout clears local package state.

## Integration

Run against an HTTPS staging provider:

- discovery;
- authorization;
- token exchange;
- JWKS rotation;
- UserInfo;
- refresh rotation;
- revoke;
- logout;
- provider outage and timeout.

No test may print real tokens, secrets, authorization codes, verifiers, or private keys.
