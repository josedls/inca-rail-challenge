<?php

namespace App\Infrastructure\Permissions;

use App\Application\Permissions\Contracts\PermissionDeleter;
use Spatie\Permission\Models\Permission;

final class SpatiePermissionDeleter implements PermissionDeleter
{
    public function delete(Permission $permission): void
    {
        $permission->delete();
    }
}
