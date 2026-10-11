<?php

namespace App\Application\Roles;

use App\Application\Roles\Contracts\RoleDeleter;
use Spatie\Permission\Models\Role;

final class DeleteRole
{
    public function __construct(
        private readonly RoleDeleter $roleDeleter,
    ) {}

    public function handle(Role $role): void
    {
        $this->roleDeleter->delete($role);
    }
}
