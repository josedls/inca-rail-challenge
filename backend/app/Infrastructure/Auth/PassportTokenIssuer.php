<?php

namespace App\Infrastructure\Auth;

use App\Application\Auth\Contracts\TokenIssuer;
use App\Domain\Auth\UserAccount;
use App\Models\User;
use LogicException;

final class PassportTokenIssuer implements TokenIssuer
{
    public function issue(UserAccount $user): string
    {
        $eloquentUser = User::query()
            ->find($user->id);

        if ($eloquentUser === null) {
            throw new LogicException('The authenticated user no longer exists.');
        }

        return $eloquentUser->createToken('spa')->accessToken;
    }
}
