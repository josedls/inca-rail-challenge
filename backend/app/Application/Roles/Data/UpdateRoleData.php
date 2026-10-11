<?php

namespace App\Application\Roles\Data;

final readonly class UpdateRoleData
{
    public function __construct(
        public string $name,
        public array $permissions,
    ) {}
}
