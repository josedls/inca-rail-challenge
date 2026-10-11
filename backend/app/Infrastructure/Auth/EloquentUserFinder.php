<?php

namespace App\Infrastructure\Auth;

use App\Application\Auth\Contracts\UserFinder;
use App\Domain\Auth\UserAccount;
use App\Models\User;

final class EloquentUserFinder implements UserFinder
{
    public function __construct(
        private readonly UserAccountMapper $userAccountMapper,
    ) {}

    public function findByEmail(string $email): ?UserAccount
    {
        $user = User::query()
            ->where('email', $email)
            ->with('roles.permissions')
            ->first();

        if ($user === null) {
            return null;
        }

        return $this->userAccountMapper->toUserAccount($user);
    }
}
