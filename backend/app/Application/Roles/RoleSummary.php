<?php

namespace App\Application\Roles;

final readonly class RoleSummary
{
    public function __construct(
        public int $id,
        public string $name,
        public string $guardName,
        public array $permissions,
    ) {}
}
