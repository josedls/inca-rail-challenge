<?php

namespace App\Application\Roles;

use App\Application\Roles\Contracts\RoleReader;

final class ListRoles
{
    public function __construct(
        private readonly RoleReader $roleReader,
    ) {}

    public function handle(): array
    {
        return $this->roleReader->all();
    }
}
