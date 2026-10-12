<?php

namespace App\Infrastructure\Auth;

final readonly class AuthGuard
{
    public function __construct(
        public string $name,
    ) {}
}
