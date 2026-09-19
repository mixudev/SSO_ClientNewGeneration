<?php

declare(strict_types=1);

namespace MixuDev\LaravelSsoClient\Support;

use Illuminate\Contracts\Config\Repository;
use MixuDev\LaravelSsoClient\Exceptions\ConfigurationException;

final readonly class SsoClientConfig
{
    public function __construct(private Repository $config)
    {
    }

    public function issuer(): string
    {
        $issuer = rtrim((string) $this->config->get('ssoclient.issuer'), '/');
        $clientId = (string) $this->config->get('ssoclient.client_id');
        $redirectUri = (string) $this->config->get('ssoclient.redirect_uri');
        $production = (bool) $this->config->get('ssoclient.production_require_https', true);
        if ($issuer === '' || $clientId === '' || $redirectUri === '') {
            throw new ConfigurationException('SSO issuer, client_id, and redirect_uri are required.');
        }
        if ($production && (parse_url($issuer, PHP_URL_SCHEME) !== 'https' || parse_url($redirectUri, PHP_URL_SCHEME) !== 'https')) {
            throw new ConfigurationException('SSO issuer and redirect_uri must use HTTPS.');
        }

        return $issuer;
    }

    public function clientId(): string
    {
        $this->issuer();

        return (string) $this->config->get('ssoclient.client_id');
    }

    public function clientSecret(): ?string
    {
        $this->issuer();
        $secret = $this->config->get('ssoclient.client_secret');

        return is_string($secret) && $secret !== '' ? $secret : null;
    }

    public function redirectUri(): string
    {
        $this->issuer();

        return (string) $this->config->get('ssoclient.redirect_uri');
    }

    /** @return list<string> */
    public function postLogoutRedirectUri(): string
    {
        $redirectUri = (string) $this->config->get('ssoclient.post_logout_redirect_uri');
        if ($redirectUri === '' || (bool) $this->config->get('ssoclient.production_require_https', true) && parse_url($redirectUri, PHP_URL_SCHEME) !== 'https') {
            throw new ConfigurationException('A valid HTTPS post-logout redirect URI is required.');
        }

        return $redirectUri;
    }

    /** @return list<string> */
    public function scopes(): array
    {
        $scopes = $this->config->get('ssoclient.scopes', ['openid']);
        if (! is_array($scopes) || array_filter($scopes, 'is_string') !== $scopes) {
            throw new ConfigurationException('SSO scopes must be a list of strings.');
        }

        return array_values(array_unique($scopes));
    }
}
