<?php

namespace App\Application\Roles\Contracts;

use App\Application\Roles\Data\CreateRoleData;
use App\Application\Roles\RoleSummary;

interface RoleCreator
{
    public function create(CreateRoleData $data): RoleSummary;
}
