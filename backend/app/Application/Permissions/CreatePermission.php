<?php

namespace App\Application\Permissions;

use App\Application\Permissions\Contracts\PermissionCreator;
use App\Application\Permissions\Data\CreatePermissionData;

final class CreatePermission
{
    public function __construct(
        private readonly PermissionCreator $permissionCreator,
    ) {}

    public function handle(CreatePermissionData $data): PermissionSummary
    {
        return $this->permissionCreator->create($data);
    }
}
