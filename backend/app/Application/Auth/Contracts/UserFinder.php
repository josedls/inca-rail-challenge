<?php

namespace App\Application\Auth\Contracts;

use App\Domain\Auth\UserAccount;

interface UserFinder
{
    public function findByEmail(string $email): ?UserAccount;
}
