<?php

namespace App\Infrastructure\Roles;

use App\Application\Roles\Contracts\RoleReader;
use App\Application\Roles\RoleSummary;
use Spatie\Permission\Models\Role;

final class SpatieRoleReader implements RoleReader
{
    public function all(): array
    {
        return Role::query()
            ->where('guard_name', config('auth.defaults.guard'))
            ->with('permissions:id,name')
            ->orderBy('name')
            ->get()
            ->map(fn (Role $role): RoleSummary => $this->toSummary($role))
            ->all();
    }

    private function toSummary(Role $role): RoleSummary
    {
        return new RoleSummary(
            id: $role->id,
            name: $role->name,
            guardName: $role->guard_name,
            permissions: $role->permissions->pluck('name')->values()->all(),
        );
    }
}
