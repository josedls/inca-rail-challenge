<?php

namespace App\Application\Users\Data;

final readonly class UpdateUserData
{
    public function __construct(
        public string $name,
        public string $email,
        public ?string $password,
        public ?array $roles,
    ) {}
}
