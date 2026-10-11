<?php

namespace App\Application\Permissions\Data;

final readonly class UpdatePermissionData
{
    public function __construct(
        public string $name,
    ) {}
}
