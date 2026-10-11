<?php

namespace App\Infrastructure\Users;

use App\Application\Users\Contracts\UserReader;
use App\Application\Users\UserSummary;
use App\Models\User;

final class EloquentUserReader implements UserReader
{
    public function all(): array
    {
        return User::query()
            ->with('roles:id,name')
            ->orderBy('id')
            ->get()
            ->map(fn (User $user): UserSummary => new UserSummary(
                id: $user->id,
                name: $user->name,
                email: $user->email,
                isActive: $user->is_active,
                roles: $user->roles->pluck('name')->values()->all(),
            ))
            ->all();
    }
}
