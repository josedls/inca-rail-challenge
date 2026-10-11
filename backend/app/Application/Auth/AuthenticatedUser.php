<?php

namespace App\Application\Auth;

use App\Domain\Auth\UserAccount;

final readonly class AuthenticatedUser
{
    public function __construct(
        public UserAccount $user,
        public string $token,
    ) {}
}
