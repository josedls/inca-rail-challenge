<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Football\FootballController;
use App\Http\Controllers\Permissions\PermissionController;
use App\Http\Controllers\Roles\RoleController;
use App\Http\Controllers\Users\UserController;
use App\Http\Middleware\EnsureUserIsActive;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware(['auth:api', EnsureUserIsActive::class])->group(function (): void {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/competitions', [FootballController::class, 'competitions'])
        ->middleware('permission:competitions.view');
    Route::post('/competitions/sync', [FootballController::class, 'syncCompetitions'])
        ->middleware('permission:competitions.view');
    Route::post('/competitions/{competition}/import', [FootballController::class, 'importCompetition'])
        ->middleware('permission:competitions.view');
    Route::get('/competitions/{competition}', [FootballController::class, 'competition'])
        ->middleware('permission:competitions.view');
    Route::get('/team', [FootballController::class, 'teams'])
        ->middleware('permission:team.view');
    Route::get('/team/{team}', [FootballController::class, 'team'])
        ->middleware('permission:team.view');
    Route::get('/players', [FootballController::class, 'players'])
        ->middleware('permission:players.view');

    Route::prefix('users')->group(function (): void {
        Route::get('/', [UserController::class, 'index'])
            ->middleware('permission:users.view');
        Route::post('/', [UserController::class, 'store'])
            ->middleware('permission:users.create');
        Route::patch('/{user}', [UserController::class, 'update'])
            ->middleware('permission:users.update');
        Route::patch('/{user}/status', [UserController::class, 'changeStatus'])
            ->middleware('permission:users.update');
    });

    Route::prefix('roles')->group(function (): void {
        Route::get('/', [RoleController::class, 'index'])
            ->middleware('permission:roles.view');
        Route::post('/', [RoleController::class, 'store'])
            ->middleware('permission:roles.create');
        Route::patch('/{role}', [RoleController::class, 'update'])
            ->middleware('permission:roles.update');
        Route::delete('/{role}', [RoleController::class, 'destroy'])
            ->middleware('permission:roles.delete');
    });

    Route::prefix('permissions')->group(function (): void {
        Route::get('/', [PermissionController::class, 'index'])
            ->middleware('permission:permissions.view');
        Route::post('/', [PermissionController::class, 'store'])
            ->middleware('permission:permissions.create');
        Route::patch('/{permission}', [PermissionController::class, 'update'])
            ->middleware('permission:permissions.update');
        Route::delete('/{permission}', [PermissionController::class, 'destroy'])
            ->middleware('permission:permissions.delete');
    });
});
