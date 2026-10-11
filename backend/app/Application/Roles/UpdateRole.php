<?php

namespace App\Application\Roles;

use App\Application\Roles\Contracts\RoleUpdater;
use App\Application\Roles\Data\UpdateRoleData;
use Spatie\Permission\Models\Role;

final class UpdateRole
{
    public function __construct(
        private readonly RoleUpdater $roleUpdater,
    ) {}

    public function handle(Role $role, UpdateRoleData $data): RoleSummary
    {
        return $this->roleUpdater->update($role, $data);
    }
}
