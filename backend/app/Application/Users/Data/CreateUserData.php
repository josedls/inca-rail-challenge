<?php

namespace App\Application\Users\Data;

final readonly class CreateUserData
{
    public function __construct(
        public string $name,
        public string $email,
        public string $password,
        public array $roles,
    ) {}
}
