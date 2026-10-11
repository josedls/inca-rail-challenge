<?php

namespace App\Application\Roles\Contracts;

interface RoleReader
{
    public function all(): array;
}
