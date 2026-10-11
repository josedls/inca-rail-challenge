<?php

namespace App\Infrastructure\Roles;

use App\Application\Roles\Contracts\RoleUpdater;
use App\Application\Roles\Data\UpdateRoleData;
use App\Application\Roles\RoleSummary;
use Spatie\Permission\Models\Role;

final class SpatieRoleUpdater implements RoleUpdater
{
    public function update(Role $role, UpdateRoleData $data): RoleSummary
    {
        $role->update([
            'name' => $data->name,
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
