<?php

namespace App\Infrastructure\Auth;

use App\Domain\Auth\UserAccount;
use App\Models\User;

final class UserAccountMapper
{
    public function toUserAccount(User $user): UserAccount
    {
        return new UserAccount(
            id: $user->id,
            name: $user->name,
            email: $user->email,
            passwordHash: $user->password,
            isActive: $user->is_active,
            roles: $user->getRoleNames()->values()->all(),
            permissions: $user->getAllPermissions()->pluck('name')->unique()->values()->all(),
        );
    }
}
