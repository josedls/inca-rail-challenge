<?php

namespace App\Infrastructure\Roles;

use App\Application\Roles\Contracts\RoleCreator;
use App\Application\Roles\Data\CreateRoleData;
use App\Application\Roles\RoleSummary;
use Spatie\Permission\Models\Role;

final class SpatieRoleCreator implements RoleCreator
{
    public function create(CreateRoleData $data): RoleSummary
    {
        $role = Role::create([
            'name' => $data->name,
            'guard_name' => config('auth.defaults.guard'),
        ]);
        $role->syncPermissions($data->permissions);
        $role->load('permissions:id,name');

        return new RoleSummary(
            id: $role->id,
            name: $role->name,
            guardName: $role->guard_name,
            permissions: $role->permissions->pluck('name')->values()->all(),
        );
    }
}
