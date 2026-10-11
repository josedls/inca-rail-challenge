<?php

namespace App\Application\Users\Contracts;

use App\Application\Users\Data\ChangeUserStatusData;
use App\Application\Users\UserSummary;
use App\Models\User;

interface UserStatusChanger
{
    public function change(User $user, ChangeUserStatusData $data): UserSummary;
}
