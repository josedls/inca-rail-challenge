<?php

namespace App\Application\Permissions\Data;

final readonly class CreatePermissionData
{
    public function __construct(
        public string $name,
    ) {}
}
