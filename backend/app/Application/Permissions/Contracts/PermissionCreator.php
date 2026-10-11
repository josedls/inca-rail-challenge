<?php

namespace App\Application\Permissions\Contracts;

use App\Application\Permissions\Data\CreatePermissionData;
use App\Application\Permissions\PermissionSummary;

interface PermissionCreator
{
    public function create(CreatePermissionData $data): PermissionSummary;
}
