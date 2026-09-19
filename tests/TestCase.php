<?php

declare(strict_types=1);

namespace MixuDev\LaravelSsoClient\Tests;

use MixuDev\LaravelSsoClient\LaravelSsoClientServiceProvider;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

abstract class TestCase extends OrchestraTestCase
{
    protected function getPackageProviders($app): array
    {
        return [LaravelSsoClientServiceProvider::class];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('app.key', 'base64:'.base64_encode(random_bytes(32)));
        $app['config']->set('app.cipher', 'AES-256-CBC');
        $app['config']->set('ssoclient.issuer', 'https://sso.example.test');
        $app['config']->set('ssoclient.client_id', 'client-test');
        $app['config']->set('ssoclient.client_secret', 'secret-test');
        $app['config']->set('ssoclient.redirect_uri', 'https://client.example.test/login/sso/callback');
        $app['config']->set('ssoclient.post_logout_redirect_uri', 'https://client.example.test/');
        $app['config']->set('ssoclient.production_require_https', true);
    }
}
