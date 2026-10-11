<?php

namespace App\Infrastructure\Auth;

use App\Application\Auth\Contracts\UserFinder;
use App\Domain\Auth\UserAccount;
use App\Models\User;

final class EloquentUserFinder implements UserFinder
{
    public function findByEmail(string $email): ?UserAccount
    {
        $user = User::query()
            ->where('email', $email)
            ->first();

        if ($user === null) {
            return null;
        }

        return new UserAccount(
            id: $user->id,
            name: $user->name,
            email: $user->email,
            passwordHash: $user->password,
            isActive: $user->is_active,
        );
    }
}
