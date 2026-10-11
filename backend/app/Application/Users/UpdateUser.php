<?php

namespace App\Application\Users;

use App\Application\Users\Contracts\UserUpdater;
use App\Application\Users\Data\UpdateUserData;
use App\Models\User;

final class UpdateUser
{
    public function __construct(
        private readonly UserUpdater $userUpdater,
    ) {}

    public function handle(User $user, UpdateUserData $data): UserSummary
    {
        return $this->userUpdater->update($user, $data);
    }
}
