<?php

namespace Tests\Unit\Infrastructure\Auth;

use App\Infrastructure\Auth\PassportTokenRevoker;
use App\Models\User;
use Illuminate\Contracts\Auth\Factory;
use Illuminate\Contracts\Auth\Guard;
use Laravel\Passport\AccessToken;
use LogicException;
use PHPUnit\Framework\TestCase;

final class PassportTokenRevokerTest extends TestCase
{
    public function test_it_revokes_the_authenticated_access_token(): void
    {
        $token = $this->createMock(AccessToken::class);
        $token->expects($this->once())->method('revoke')->willReturn(true);

        $user = $this->createMock(User::class);
        $user->expects($this->once())->method('token')->willReturn($token);

        $guard = $this->createMock(Guard::class);
        $guard->expects($this->once())->method('user')->willReturn($user);

        $auth = $this->createMock(Factory::class);
        $auth->expects($this->once())->method('guard')->with('api')->willReturn($guard);

        (new PassportTokenRevoker($auth))->revoke();
    }

    public function test_it_rejects_missing_access_token(): void
    {
        $user = $this->createMock(User::class);
        $user->expects($this->once())->method('token')->willReturn(null);

        $guard = $this->createMock(Guard::class);
        $guard->method('user')->willReturn($user);

        $auth = $this->createMock(Factory::class);
        $auth->method('guard')->willReturn($guard);

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('No revocable Passport access token was found.');

        (new PassportTokenRevoker($auth))->revoke();
    }
}
