<?php

namespace App\Application\Permissions;

use App\Application\Permissions\Contracts\PermissionReader;

final class ListPermissions
{
    public function __construct(
        private readonly PermissionReader $permissionReader,
    ) {}

    public function handle(): array
    {
        return $this->permissionReader->all();
    }
}
