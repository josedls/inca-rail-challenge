<?php

namespace App\Application\Roles\Contracts;

use App\Application\Roles\Data\UpdateRoleData;
use App\Application\Roles\RoleSummary;
use Spatie\Permission\Models\Role;

interface RoleUpdater
{
    public function update(Role $role, UpdateRoleData $data): RoleSummary;
}
