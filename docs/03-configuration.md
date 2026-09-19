# Configuration

Publish config:

```bash
php artisan vendor:publish --tag=ssoclient-config
```

Environment:

```dotenv
SSO_ISSUER=https://sso.example.com
SSO_CLIENT_ID=client-id
SSO_CLIENT_SECRET=client-secret
SSO_REDIRECT_URI=https://client.example.com/login/sso/callback
```

Configuration rules:

- `SSO_ISSUER` must be the stable provider issuer.
- `SSO_CLIENT_ID` must match the registered application credential.
- `SSO_CLIENT_SECRET` is required for confidential web clients and must be secret-managed.
- `SSO_REDIRECT_URI` must exactly match the provider registry.
- `scopes` must be minimal and include `openid` for OIDC.
- Production HTTPS enforcement is enabled by default.
- Timeout, retry count, state TTL, discovery TTL, and JWKS TTL are bounded values.

Never put the client secret in `config/ssoclient.php` or commit a real `.env` file.
