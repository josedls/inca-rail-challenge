<?php

namespace App\Providers;

use App\Application\Auth\Contracts\CurrentUserReader;
use App\Application\Auth\Contracts\PasswordVerifier;
use App\Application\Auth\Contracts\TokenIssuer;
use App\Application\Auth\Contracts\TokenRevoker;
use App\Application\Auth\Contracts\UserFinder;
use App\Application\Football\Contracts\CompetitionImportDispatcher;
use App\Application\Football\Contracts\CompetitionSyncDispatcher;
use App\Application\Football\Contracts\FootballCatalogReader;
use App\Application\Football\Contracts\FootballCatalogWriter;
use App\Application\Football\Contracts\FootballDataProvider;
use App\Application\Permissions\Contracts\PermissionCreator;
use App\Application\Permissions\Contracts\PermissionDeleter;
use App\Application\Permissions\Contracts\PermissionReader;
use App\Application\Permissions\Contracts\PermissionUpdater;
use App\Application\Roles\Contracts\RoleCreator;
use App\Application\Roles\Contracts\RoleDeleter;
use App\Application\Roles\Contracts\RoleReader;
use App\Application\Roles\Contracts\RoleUpdater;
use App\Application\Users\Contracts\UserCreator;
use App\Application\Users\Contracts\UserReader;
use App\Application\Users\Contracts\UserStatusChanger;
use App\Application\Users\Contracts\UserUpdater;
use App\Infrastructure\Auth\AuthGuard;
use App\Infrastructure\Auth\EloquentCurrentUserReader;
use App\Infrastructure\Auth\EloquentUserFinder;
use App\Infrastructure\Auth\LaravelPasswordVerifier;
use App\Infrastructure\Auth\PassportTokenIssuer;
use App\Infrastructure\Auth\PassportTokenRevoker;
use App\Infrastructure\Auth\UserAccountMapper;
use App\Infrastructure\Football\EloquentFootballCatalogReader;
use App\Infrastructure\Football\EloquentFootballCatalogWriter;
use App\Infrastructure\Football\FootballDataClient;
use App\Infrastructure\Football\QueueCompetitionImport;
use App\Infrastructure\Football\QueueCompetitionSync;
use App\Infrastructure\Permissions\SpatiePermissionCreator;
use App\Infrastructure\Permissions\SpatiePermissionDeleter;
use App\Infrastructure\Permissions\SpatiePermissionReader;
use App\Infrastructure\Permissions\SpatiePermissionUpdater;
use App\Infrastructure\Roles\SpatieRoleCreator;
use App\Infrastructure\Roles\SpatieRoleDeleter;
use App\Infrastructure\Roles\SpatieRoleReader;
use App\Infrastructure\Roles\SpatieRoleUpdater;
use App\Infrastructure\Users\EloquentUserCreator;
use App\Infrastructure\Users\EloquentUserReader;
use App\Infrastructure\Users\EloquentUserStatusChanger;
use App\Infrastructure\Users\EloquentUserUpdater;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(
            AuthGuard::class,
            fn (): AuthGuard => new AuthGuard(config('auth.defaults.guard')),
        );
        $this->app->bind(UserFinder::class, EloquentUserFinder::class);
        $this->app->bind(CurrentUserReader::class, EloquentCurrentUserReader::class);
        $this->app->singleton(UserAccountMapper::class);
        $this->app->bind(PasswordVerifier::class, LaravelPasswordVerifier::class);
        $this->app->bind(TokenRevoker::class, PassportTokenRevoker::class);
        $this->app->bind(TokenIssuer::class, PassportTokenIssuer::class);
        $this->app->singleton(FootballDataProvider::class, function (): FootballDataProvider {
            return new FootballDataClient(
                Http::baseUrl(config('services.football_data.base_url'))
                    ->acceptJson()
                    ->withHeaders([
                        'X-Auth-Token' => config('services.football_data.token'),
                    ])
                    ->timeout(config('services.football_data.timeout')),
                config('services.football_data.token'),
            );
        });
        $this->app->bind(FootballCatalogReader::class, EloquentFootballCatalogReader::class);
        $this->app->bind(FootballCatalogWriter::class, EloquentFootballCatalogWriter::class);
        $this->app->bind(CompetitionImportDispatcher::class, QueueCompetitionImport::class);
        $this->app->bind(CompetitionSyncDispatcher::class, QueueCompetitionSync::class);
        $this->app->bind(UserReader::class, EloquentUserReader::class);
        $this->app->bind(UserCreator::class, EloquentUserCreator::class);
        $this->app->bind(UserUpdater::class, EloquentUserUpdater::class);
        $this->app->bind(UserStatusChanger::class, EloquentUserStatusChanger::class);
        $this->app->bind(RoleReader::class, SpatieRoleReader::class);
        $this->app->bind(RoleCreator::class, SpatieRoleCreator::class);
        $this->app->bind(RoleUpdater::class, SpatieRoleUpdater::class);
        $this->app->bind(RoleDeleter::class, SpatieRoleDeleter::class);
        $this->app->bind(PermissionReader::class, SpatiePermissionReader::class);
        $this->app->bind(PermissionCreator::class, SpatiePermissionCreator::class);
        $this->app->bind(PermissionUpdater::class, SpatiePermissionUpdater::class);
        $this->app->bind(PermissionDeleter::class, SpatiePermissionDeleter::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('football-data', function (): Limit {
            return Limit::perMinute(10)->by('football-data');
        });
    }
}
