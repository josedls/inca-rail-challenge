<?php

namespace App\Domain\Auth;

final readonly class UserAccount
{
    public function __construct(
        public int $id,
        public string $name,
        public string $email,
        public string $passwordHash,
        public bool $isActive,
        public array $roles = [],
        public array $permissions = [],
    ) {}
}
