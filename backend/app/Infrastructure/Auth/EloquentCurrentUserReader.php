<?php

namespace App\Infrastructure\Auth;

use App\Application\Auth\Contracts\CurrentUserReader;
use App\Domain\Auth\UserAccount;
use Illuminate\Contracts\Auth\Factory;
use LogicException;

final class EloquentCurrentUserReader implements CurrentUserReader
{
    public function __construct(
        private readonly Factory $auth,
        private readonly UserAccountMapper $userAccountMapper,
        private readonly AuthGuard $authGuard,
    ) {}

    public function current(): UserAccount
    {
        $user = $this->auth->guard($this->authGuard->name)->user();

        if ($user === null) {
            throw new LogicException('No authenticated user was found.');
        }

        $user->loadMissing('roles.permissions');

        return $this->userAccountMapper->toUserAccount($user);
    }
}
