<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Users\UserController;
use App\Http\Middleware\EnsureUserIsActive;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware(['auth:api', EnsureUserIsActive::class]);
Route::get('/users', [UserController::class, 'index'])
    ->middleware(['auth:api', EnsureUserIsActive::class, 'permission:users.view']);
Route::post('/users', [UserController::class, 'store'])
    ->middleware(['auth:api', EnsureUserIsActive::class, 'permission:users.create']);
