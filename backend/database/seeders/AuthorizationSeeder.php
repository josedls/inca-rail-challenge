<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

final class AuthorizationSeeder extends Seeder
{
    private const GUARD = 'api';

    private const PERMISSIONS = [
        'users.view',
        'users.create',
        'users.update',
        'users.delete',
        'roles.view',
        'roles.create',
        'roles.update',
        'roles.delete',
        'permissions.view',
        'permissions.create',
        'permissions.update',
        'permissions.delete',
        'competitions.view',
        'team.view',
        'players.view',
    ];

    public function run(): void
    {
        $permissions = collect(self::PERMISSIONS)
            ->map(fn (string $name): Permission => Permission::findOrCreate($name, self::GUARD));

        $admin = Role::findOrCreate('admin', self::GUARD);
        $viewer = Role::findOrCreate('viewer', self::GUARD);

        $admin->syncPermissions($permissions);
        $viewer->syncPermissions($permissions->filter(
            fn (Permission $permission): bool => in_array($permission->name, [
                'competitions.view',
                'team.view',
                'players.view',
            ], true),
        ));
    }
}
