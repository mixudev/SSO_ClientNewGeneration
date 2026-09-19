<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use MixuDev\LaravelSsoClient\Http\SsoClientController;

Route::middleware(['web'])->group(function (): void {
    Route::get(config('ssoclient.routes.redirect', '/login/sso'), [SsoClientController::class, 'redirect'])
        ->name('ssoclient.redirect');
    Route::get(config('ssoclient.routes.callback', '/login/sso/callback'), [SsoClientController::class, 'callback'])
        ->name('ssoclient.callback');
    Route::post(config('ssoclient.routes.logout', '/logout/sso'), [SsoClientController::class, 'logout'])
        ->name('ssoclient.logout');
});
