<?php

namespace App\Application\Roles;

use App\Application\Roles\Contracts\RoleCreator;
use App\Application\Roles\Data\CreateRoleData;

final class CreateRole
{
    public function __construct(
        private readonly RoleCreator $roleCreator,
    ) {}

    public function handle(CreateRoleData $data): RoleSummary
    {
        return $this->roleCreator->create($data);
    }
}
