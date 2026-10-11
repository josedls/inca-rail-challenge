<?php

namespace App\Application\Users;

use App\Application\Users\Contracts\UserStatusChanger;
use App\Application\Users\Data\ChangeUserStatusData;
use App\Models\User;

final class ChangeUserStatus
{
    public function __construct(
        private readonly UserStatusChanger $userStatusChanger,
    ) {}

    public function handle(User $user, ChangeUserStatusData $data): UserSummary
    {
        return $this->userStatusChanger->change($user, $data);
    }
}
