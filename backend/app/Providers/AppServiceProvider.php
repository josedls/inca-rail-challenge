<?php

namespace App\Providers;

use App\Application\Auth\Contracts\PasswordVerifier;
use App\Application\Auth\Contracts\TokenIssuer;
use App\Application\Auth\Contracts\TokenRevoker;
use App\Application\Auth\Contracts\UserFinder;
use App\Infrastructure\Auth\EloquentUserFinder;
use App\Infrastructure\Auth\LaravelPasswordVerifier;
use App\Infrastructure\Auth\PassportTokenIssuer;
use App\Infrastructure\Auth\PassportTokenRevoker;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(UserFinder::class, EloquentUserFinder::class);
        $this->app->bind(PasswordVerifier::class, LaravelPasswordVerifier::class);
        $this->app->bind(TokenRevoker::class, PassportTokenRevoker::class);
        $this->app->bind(TokenIssuer::class, PassportTokenIssuer::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
