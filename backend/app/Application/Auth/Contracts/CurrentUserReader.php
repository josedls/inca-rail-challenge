<?php

namespace App\Application\Auth\Contracts;

use App\Domain\Auth\UserAccount;

interface CurrentUserReader
{
    public function current(): UserAccount;
}
