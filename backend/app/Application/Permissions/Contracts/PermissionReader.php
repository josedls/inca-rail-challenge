<?php

namespace App\Application\Permissions\Contracts;

interface PermissionReader
{
    public function all(): array;
}
