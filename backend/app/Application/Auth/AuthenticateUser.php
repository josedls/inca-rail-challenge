<?php

namespace App\Application\Auth;

use App\Application\Auth\Contracts\PasswordVerifier;
use App\Application\Auth\Contracts\TokenIssuer;
use App\Application\Auth\Contracts\UserFinder;
use App\Domain\Auth\InvalidCredentials;

final class AuthenticateUser
{
    public function __construct(
        private readonly UserFinder $userFinder,
        private readonly PasswordVerifier $passwordVerifier,
        private readonly TokenIssuer $tokenIssuer,
    ) {}

    public function handle(string $email, string $password): AuthenticatedUser
    {
        $user = $this->userFinder->findByEmail($email);

        if ($user === null || ! $this->passwordVerifier->matches($password, $user->passwordHash)) {
            throw new InvalidCredentials('The provided credentials are incorrect.');
        }

        return new AuthenticatedUser(
            user: $user,
            token: $this->tokenIssuer->issue($user),
        );
    }
}
