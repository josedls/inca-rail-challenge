<?php

namespace App\Application\Permissions\Contracts;

use App\Application\Permissions\Data\UpdatePermissionData;
use App\Application\Permissions\PermissionSummary;
use Spatie\Permission\Models\Permission;

interface PermissionUpdater
{
    public function update(Permission $permission, UpdatePermissionData $data): PermissionSummary;
}
