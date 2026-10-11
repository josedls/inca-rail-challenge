<?php

namespace App\Application\Users\Contracts;

interface UserReader
{
    public function all(): array;
}
