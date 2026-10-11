<?php

namespace App\Infrastructure\Users;

use App\Application\Users\Contracts\UserStatusChanger;
use App\Application\Users\Data\ChangeUserStatusData;
use App\Application\Users\UserSummary;
use App\Models\User;

final class EloquentUserStatusChanger implements UserStatusChanger
{
    public function change(User $user, ChangeUserStatusData $data): UserSummary
    {
        $user->update([
            'is_active' => $data->isActive,
        ]);
        $user->load('roles:id,name');

        return new UserSummary(
            id: $user->id,
            name: $user->name,
            email: $user->email,
            isActive: $user->is_active,
            roles: $user->roles->pluck('name')->values()->all(),
        );
    }
}
