<?php

namespace App\Application\Users;

use App\Application\Users\Contracts\UserReader;

final class ListUsers
{
    public function __construct(
        private readonly UserReader $userReader,
    ) {}

    public function handle(): array
    {
        return $this->userReader->all();
    }
}
