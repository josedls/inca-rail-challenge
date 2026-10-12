<?php

namespace App\Infrastructure\Permissions;

use App\Application\Permissions\Contracts\PermissionReader;
use App\Application\Permissions\PermissionSummary;
use Spatie\Permission\Models\Permission;

final class SpatiePermissionReader implements PermissionReader
{
    public function all(): array
    {
        return Permission::query()
            ->where('guard_name', config('auth.defaults.guard'))
            ->orderBy('name')
            ->get()
            ->map(fn (Permission $permission): PermissionSummary => $this->toSummary($permission))
            ->all();
    }

    private function toSummary(Permission $permission): PermissionSummary
    {
        return new PermissionSummary(
            id: $permission->id,
            name: $permission->name,
            guardName: $permission->guard_name,
        );
    }
}
