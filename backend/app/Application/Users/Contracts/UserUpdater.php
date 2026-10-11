<?php

namespace App\Application\Users\Contracts;

use App\Application\Users\Data\UpdateUserData;
use App\Application\Users\UserSummary;
use App\Models\User;

interface UserUpdater
{
    public function update(User $user, UpdateUserData $data): UserSummary;
}
