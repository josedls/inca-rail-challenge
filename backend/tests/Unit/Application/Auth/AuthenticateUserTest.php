<?php

namespace Tests\Unit\Application\Auth;

use App\Application\Auth\AuthenticateUser;
use App\Application\Auth\Contracts\PasswordVerifier;
use App\Application\Auth\Contracts\TokenIssuer;
use App\Application\Auth\Contracts\UserFinder;
use App\Domain\Auth\InvalidCredentials;
use App\Domain\Auth\UserAccount;
use PHPUnit\Framework\TestCase;

final class AuthenticateUserTest extends TestCase
{
    public function test_it_authenticates_user_and_issues_token(): void
    {
        $user = new UserAccount(
            id: 1,
            name: 'Valeria Quispe',
            email: 'valeria.quispe@inca-rail.test',
            passwordHash: 'hashed-password',
            isActive: true,
        );

        $userFinder = $this->createMock(UserFinder::class);
        $userFinder
            ->expects($this->once())
            ->method('findByEmail')
            ->with('valeria.quispe@inca-rail.test')
            ->willReturn($user);

        $passwordVerifier = $this->createMock(PasswordVerifier::class);
        $passwordVerifier
            ->expects($this->once())
            ->method('matches')
            ->with('Railway#2026', 'hashed-password')
            ->willReturn(true);

        $tokenIssuer = $this->createMock(TokenIssuer::class);
        $tokenIssuer
            ->expects($this->once())
            ->method('issue')
            ->with($user)
            ->willReturn('access-token');

        $authenticatedUser = (new AuthenticateUser(
            userFinder: $userFinder,
            passwordVerifier: $passwordVerifier,
            tokenIssuer: $tokenIssuer,
        ))->handle('valeria.quispe@inca-rail.test', 'Railway#2026');

        $this->assertSame($user, $authenticatedUser->user);
        $this->assertSame('access-token', $authenticatedUser->token);
    }

    public function test_it_rejects_unknown_user(): void
    {
        $userFinder = $this->createMock(UserFinder::class);
        $userFinder
            ->expects($this->once())
            ->method('findByEmail')
            ->with('unknown@example.com')
            ->willReturn(null);

        $passwordVerifier = $this->createMock(PasswordVerifier::class);
        $passwordVerifier->expects($this->never())->method('matches');

        $tokenIssuer = $this->createMock(TokenIssuer::class);
        $tokenIssuer->expects($this->never())->method('issue');

        $this->expectException(InvalidCredentials::class);

        (new AuthenticateUser(
            userFinder: $userFinder,
            passwordVerifier: $passwordVerifier,
            tokenIssuer: $tokenIssuer,
        ))->handle('unknown@example.com', 'secret');
    }

    public function test_it_rejects_invalid_password(): void
    {
        $user = new UserAccount(
            id: 1,
            name: 'Valeria Quispe',
            email: 'valeria.quispe@inca-rail.test',
            passwordHash: 'hashed-password',
            isActive: true,
        );

        $userFinder = $this->createMock(UserFinder::class);
        $userFinder->method('findByEmail')->willReturn($user);

        $passwordVerifier = $this->createMock(PasswordVerifier::class);
        $passwordVerifier
            ->expects($this->once())
            ->method('matches')
            ->with('WrongRailway#2026', 'hashed-password')
            ->willReturn(false);

        $tokenIssuer = $this->createMock(TokenIssuer::class);
        $tokenIssuer->expects($this->never())->method('issue');

        $this->expectException(InvalidCredentials::class);

        (new AuthenticateUser(
            userFinder: $userFinder,
            passwordVerifier: $passwordVerifier,
            tokenIssuer: $tokenIssuer,
        ))->handle('valeria.quispe@inca-rail.test', 'WrongRailway#2026');
    }

    public function test_it_rejects_inactive_users(): void
    {
        $user = new UserAccount(
            id: 1,
            name: 'Valeria Quispe',
            email: 'valeria.quispe@inca-rail.test',
            passwordHash: 'hashed-password',
            isActive: false,
        );

        $userFinder = $this->createMock(UserFinder::class);
        $userFinder->method('findByEmail')->willReturn($user);

        $passwordVerifier = $this->createMock(PasswordVerifier::class);
        $passwordVerifier->expects($this->never())->method('matches');

        $tokenIssuer = $this->createMock(TokenIssuer::class);
        $tokenIssuer->expects($this->never())->method('issue');

        $this->expectException(InvalidCredentials::class);

        (new AuthenticateUser(
            userFinder: $userFinder,
            passwordVerifier: $passwordVerifier,
            tokenIssuer: $tokenIssuer,
        ))->handle('valeria.quispe@inca-rail.test', 'Railway#2026');
    }
}
