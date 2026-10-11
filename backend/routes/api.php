<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Roles\RoleController;
use App\Http\Controllers\Users\UserController;
use App\Http\Middleware\EnsureUserIsActive;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware(['auth:api', EnsureUserIsActive::class])->group(function (): void {
    Route::post('/logout', [AuthController::class, 'logout']);

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
});
