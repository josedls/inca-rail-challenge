<?php

namespace App\Application\Permissions;

final readonly class PermissionSummary
{
    public function __construct(
        public int $id,
        public string $name,
        public string $guardName,
    ) {}
}
