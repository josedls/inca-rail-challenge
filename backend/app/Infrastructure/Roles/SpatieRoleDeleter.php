<?php

namespace App\Infrastructure\Roles;

use App\Application\Roles\Contracts\RoleDeleter;
use Spatie\Permission\Models\Role;

final class SpatieRoleDeleter implements RoleDeleter
{
    public function delete(Role $role): void
    {
        $role->delete();
    }
}
