<?php

declare(strict_types=1);

namespace MixuDev\LaravelSsoClient;

use Illuminate\Support\ServiceProvider;
use MixuDev\LaravelSsoClient\Http\SsoClientController;
use MixuDev\LaravelSsoClient\Security\PkceGenerator;
use MixuDev\LaravelSsoClient\Security\StateManager;
use MixuDev\LaravelSsoClient\Support\SessionTokenStore;
use MixuDev\LaravelSsoClient\Support\SsoClientConfig;
use MixuDev\LaravelSsoClient\Contracts\TokenStore;

final class LaravelSsoClientServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/ssoclient.php', 'ssoclient');
        $this->app->singleton(SsoClientConfig::class);
        $this->app->singleton(PkceGenerator::class);
        $this->app->singleton(StateManager::class);
        $this->app->singleton(TokenStore::class, SessionTokenStore::class);
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/ssoclient.php' => config_path('ssoclient.php'),
        ], 'ssoclient-config');

        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
    }
}
