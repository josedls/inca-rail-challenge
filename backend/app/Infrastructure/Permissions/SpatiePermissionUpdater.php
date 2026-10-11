<?php

namespace App\Infrastructure\Permissions;

use App\Application\Permissions\Contracts\PermissionUpdater;
use App\Application\Permissions\Data\UpdatePermissionData;
use App\Application\Permissions\PermissionSummary;
use Spatie\Permission\Models\Permission;

final class SpatiePermissionUpdater implements PermissionUpdater
{
    public function update(Permission $permission, UpdatePermissionData $data): PermissionSummary
    {
        $permission->update([
            'name' => $data->name,
        ]);

        return new PermissionSummary(
            id: $permission->id,
            name: $permission->name,
            guardName: $permission->guard_name,
        );
    }
}
