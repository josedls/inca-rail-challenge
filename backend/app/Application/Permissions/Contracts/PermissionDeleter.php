<?php

namespace App\Application\Permissions\Contracts;

use Spatie\Permission\Models\Permission;

interface PermissionDeleter
{
    public function delete(Permission $permission): void;
}
