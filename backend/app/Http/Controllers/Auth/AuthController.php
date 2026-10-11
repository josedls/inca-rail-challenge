<?php

namespace App\Http\Controllers\Auth;

use App\Application\Auth\AuthenticateUser;
use App\Application\Auth\GetCurrentUser;
use App\Application\Auth\LogoutUser;
use App\Domain\Auth\InvalidCredentials;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;

final class AuthController extends Controller
{
    public function me(GetCurrentUser $getCurrentUser): JsonResponse
    {
        return response()->json([
            'data' => new UserResource($getCurrentUser->handle()),
        ]);
    }

    public function login(LoginRequest $request, AuthenticateUser $authenticateUser): JsonResponse
    {
        try {
            $result = $authenticateUser->handle(
                $request->string('email')->toString(),
                $request->string('password')->toString(),
            );
        } catch (InvalidCredentials) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        return response()->json([
            'data' => [
                'token' => $result->token,
                'token_type' => 'Bearer',
                'user' => new UserResource($result->user),
            ],
        ]);
    }

    public function logout(LogoutUser $logoutUser): Response
    {
        $logoutUser->handle();

        return response()->noContent();
    }
}
