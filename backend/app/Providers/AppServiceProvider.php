<?php

namespace App\Providers;

use App\Application\Auth\Contracts\PasswordVerifier;
use App\Application\Auth\Contracts\TokenIssuer;
use App\Application\Auth\Contracts\TokenRevoker;
use App\Application\Auth\Contracts\UserFinder;
use App\Application\Roles\Contracts\RoleCreator;
use App\Application\Roles\Contracts\RoleDeleter;
use App\Application\Roles\Contracts\RoleReader;
use App\Application\Roles\Contracts\RoleUpdater;
use App\Application\Users\Contracts\UserCreator;
use App\Application\Users\Contracts\UserReader;
use App\Application\Users\Contracts\UserStatusChanger;
use App\Application\Users\Contracts\UserUpdater;
use App\Infrastructure\Auth\EloquentUserFinder;
use App\Infrastructure\Auth\LaravelPasswordVerifier;
use App\Infrastructure\Auth\PassportTokenIssuer;
use App\Infrastructure\Auth\PassportTokenRevoker;
use App\Infrastructure\Roles\SpatieRoleCreator;
use App\Infrastructure\Roles\SpatieRoleDeleter;
use App\Infrastructure\Roles\SpatieRoleReader;
use App\Infrastructure\Roles\SpatieRoleUpdater;
use App\Infrastructure\Users\EloquentUserCreator;
use App\Infrastructure\Users\EloquentUserReader;
use App\Infrastructure\Users\EloquentUserStatusChanger;
use App\Infrastructure\Users\EloquentUserUpdater;
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
        $this->app->bind(UserReader::class, EloquentUserReader::class);
        $this->app->bind(UserCreator::class, EloquentUserCreator::class);
        $this->app->bind(UserUpdater::class, EloquentUserUpdater::class);
        $this->app->bind(UserStatusChanger::class, EloquentUserStatusChanger::class);
        $this->app->bind(RoleReader::class, SpatieRoleReader::class);
        $this->app->bind(RoleCreator::class, SpatieRoleCreator::class);
        $this->app->bind(RoleUpdater::class, SpatieRoleUpdater::class);
        $this->app->bind(RoleDeleter::class, SpatieRoleDeleter::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
