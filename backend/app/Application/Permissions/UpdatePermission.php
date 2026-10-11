<?php

namespace App\Application\Permissions;

use App\Application\Permissions\Contracts\PermissionUpdater;
use App\Application\Permissions\Data\UpdatePermissionData;
use Spatie\Permission\Models\Permission;

final class UpdatePermission
{
    public function __construct(
        private readonly PermissionUpdater $permissionUpdater,
    ) {}

    public function handle(Permission $permission, UpdatePermissionData $data): PermissionSummary
    {
        return $this->permissionUpdater->update($permission, $data);
    }
}
