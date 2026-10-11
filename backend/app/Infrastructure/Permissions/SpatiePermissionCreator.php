<?php

namespace App\Infrastructure\Permissions;

use App\Application\Permissions\Contracts\PermissionCreator;
use App\Application\Permissions\Data\CreatePermissionData;
use App\Application\Permissions\PermissionSummary;
use Spatie\Permission\Models\Permission;

final class SpatiePermissionCreator implements PermissionCreator
{
    public function create(CreatePermissionData $data): PermissionSummary
    {
        $permission = Permission::create([
            'name' => $data->name,
            'guard_name' => 'api',
        ]);

        return new PermissionSummary(
            id: $permission->id,
            name: $permission->name,
            guardName: $permission->guard_name,
        );
    }
}
