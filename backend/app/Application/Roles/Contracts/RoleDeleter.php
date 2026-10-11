<?php

namespace App\Application\Roles\Contracts;

use Spatie\Permission\Models\Role;

interface RoleDeleter
{
    public function delete(Role $role): void;
}
