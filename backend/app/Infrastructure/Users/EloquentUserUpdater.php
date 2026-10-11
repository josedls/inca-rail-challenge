<?php

namespace App\Infrastructure\Users;

use App\Application\Users\Contracts\UserUpdater;
use App\Application\Users\Data\UpdateUserData;
use App\Application\Users\UserSummary;
use App\Models\User;

final class EloquentUserUpdater implements UserUpdater
{
    public function update(User $user, UpdateUserData $data): UserSummary
    {
        $user->fill([
            'name' => $data->name,
            'email' => $data->email,
        ]);

        if ($data->password !== null) {
            $user->password = $data->password;
        }

        $user->save();

        if ($data->roles !== null) {
            $user->syncRoles($data->roles);
        }

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
