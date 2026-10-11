<?php

namespace Tests\Feature\Auth;

use App\Application\Auth\AuthenticateUser;
use App\Application\Auth\Contracts\PasswordVerifier;
use App\Application\Auth\Contracts\TokenIssuer;
use App\Application\Auth\Contracts\UserFinder;
use App\Domain\Auth\UserAccount;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

final class LoginTest extends TestCase
{
    public function test_it_returns_an_access_token_for_valid_credentials(): void
    {
        $user = new UserAccount(
            id: 1,
            name: 'Valeria Quispe',
            email: 'valeria.quispe@inca-rail.test',
            passwordHash: 'hashed-password',
        );

        $userFinder = $this->createMock(UserFinder::class);
        $userFinder->method('findByEmail')->willReturn($user);

        $passwordVerifier = $this->createMock(PasswordVerifier::class);
        $passwordVerifier->method('matches')->willReturn(true);

        $tokenIssuer = $this->createMock(TokenIssuer::class);
        $tokenIssuer->method('issue')->willReturn('passport-token-for-valeria');

        $this->app->instance(
            AuthenticateUser::class,
            new AuthenticateUser($userFinder, $passwordVerifier, $tokenIssuer),
        );

        $response = $this->postJson('/login', [
            'email' => 'valeria.quispe@inca-rail.test',
            'password' => 'Railway#2026',
        ]);

        $this->assertSuccessfulLoginResponse($response, $user);
    }

    public function test_it_returns_validation_error_for_invalid_credentials(): void
    {
        $user = new UserAccount(
            id: 1,
            name: 'Valeria Quispe',
            email: 'valeria.quispe@inca-rail.test',
            passwordHash: 'hashed-password',
        );

        $userFinder = $this->createMock(UserFinder::class);
        $userFinder->method('findByEmail')->willReturn($user);

        $passwordVerifier = $this->createMock(PasswordVerifier::class);
        $passwordVerifier->method('matches')->willReturn(false);

        $tokenIssuer = $this->createMock(TokenIssuer::class);
        $tokenIssuer->expects($this->never())->method('issue');

        $this->app->instance(
            AuthenticateUser::class,
            new AuthenticateUser($userFinder, $passwordVerifier, $tokenIssuer),
        );

        $response = $this->postJson('/login', [
            'email' => 'valeria.quispe@inca-rail.test',
            'password' => 'WrongRailway#2026',
        ]);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['email'])
            ->assertJsonPath('errors.email.0', 'The provided credentials are incorrect.');
    }

    public function test_it_validates_login_input(): void
    {
        $response = $this->postJson('/login', [
            'email' => 'invalid-email',
            'password' => '',
        ]);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['email', 'password']);
    }

    private function assertSuccessfulLoginResponse(TestResponse $response, UserAccount $user): void
    {
        $response
            ->assertOk()
            ->assertJsonPath('data.token', 'passport-token-for-valeria')
            ->assertJsonPath('data.token_type', 'Bearer')
            ->assertJsonPath('data.user.id', $user->id)
            ->assertJsonPath('data.user.name', $user->name)
            ->assertJsonPath('data.user.email', $user->email)
            ->assertJsonMissingPath('data.user.password');
    }
}
