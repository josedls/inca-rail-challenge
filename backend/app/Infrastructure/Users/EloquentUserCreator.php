<?php

namespace App\Infrastructure\Users;

use App\Application\Users\Contracts\UserCreator;
use App\Application\Users\Data\CreateUserData;
use App\Application\Users\UserSummary;
use App\Models\User;

final class EloquentUserCreator implements UserCreator
{
    public function create(CreateUserData $data): UserSummary
    {
        $user = User::create([
            'name' => $data->name,
            'email' => $data->email,
            'password' => $data->password,
            'is_active' => true,
        ]);

        $user->assignRole($data->roles);
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
