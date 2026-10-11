<?php

namespace App\Application\Permissions;

use App\Application\Permissions\Contracts\PermissionDeleter;
use Spatie\Permission\Models\Permission;

final class DeletePermission
{
    public function __construct(
        private readonly PermissionDeleter $permissionDeleter,
    ) {}

    public function handle(Permission $permission): void
    {
        $this->permissionDeleter->delete($permission);
    }
}
