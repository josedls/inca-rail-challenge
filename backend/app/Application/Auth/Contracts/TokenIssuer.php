<?php

namespace App\Application\Auth\Contracts;

use App\Domain\Auth\UserAccount;

interface TokenIssuer
{
    public function issue(UserAccount $user): string;
}
