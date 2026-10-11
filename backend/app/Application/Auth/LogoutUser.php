<?php

namespace App\Application\Auth;

use App\Application\Auth\Contracts\TokenRevoker;

final class LogoutUser
{
    public function __construct(
        private readonly TokenRevoker $tokenRevoker,
    ) {}

    public function handle(): void
    {
        $this->tokenRevoker->revoke();
    }
}
