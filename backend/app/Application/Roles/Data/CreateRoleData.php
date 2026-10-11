<?php

namespace App\Application\Roles\Data;

final readonly class CreateRoleData
{
    public function __construct(
        public string $name,
        public array $permissions,
    ) {}
}
