<?php

namespace App\Infrastructure\Auth;

use App\Application\Auth\Contracts\TokenRevoker;
use Illuminate\Contracts\Auth\Factory;
use Laravel\Passport\AccessToken;
use Laravel\Passport\Contracts\OAuthenticatable;
use LogicException;

final class PassportTokenRevoker implements TokenRevoker
{
    public function __construct(
        private readonly Factory $auth,
        private readonly AuthGuard $authGuard,
    ) {}

    public function revoke(): void
    {
        $user = $this->auth->guard($this->authGuard->name)->user();

        if (! $user instanceof OAuthenticatable) {
            throw new LogicException('No authenticated Passport user was found.');
        }

        $token = $user->token();

        if (! $token instanceof AccessToken) {
            throw new LogicException('No revocable Passport access token was found.');
        }

        if (! $token->revoke()) {
            throw new LogicException('The Passport access token could not be revoked.');
        }
    }
}
