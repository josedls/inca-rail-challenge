<?php

namespace App\Application\Users\Contracts;

use App\Application\Users\Data\CreateUserData;
use App\Application\Users\UserSummary;

interface UserCreator
{
    public function create(CreateUserData $data): UserSummary;
}
