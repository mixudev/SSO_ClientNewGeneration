# Release and Publishing

## Required before release

- All package source files pass `php -l`.
- PHPUnit unit and feature tests pass.
- `composer validate --strict` passes.
- No `.env`, key, certificate, token, secret, or credential is tracked.
- README and security documentation are current.
- Laravel 12 and 13 compatibility is tested.
- External HTTPS smoke test passes.
- Unknown-`kid` refresh behavior is implemented and tested.
- User resolver and encrypted token store are implemented.
- UserInfo, revoke, refresh, and provider logout are implemented or explicitly excluded from the release contract.
- Changelog and version tag are prepared.

## Composer package metadata

The package uses:

```text
mixudev/laravel-sso-client
```

The namespace is:

```text
MixuDev\LaravelSsoClient
```

Laravel auto-discovery is configured through `extra.laravel.providers`.

## Validation

```bash
composer install
composer validate --strict
composer lint
composer test
```

Do not publish an incomplete foundation as a stable security package. Use a pre-release version if the roadmap is not complete.
