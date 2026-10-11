<?php

namespace App\Application\Users;

final readonly class UserSummary
{
    public function __construct(
        public int $id,
        public string $name,
        public string $email,
        public bool $isActive,
        public array $roles,
    ) {}
}
