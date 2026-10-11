<?php

namespace App\Application\Auth;

use App\Application\Auth\Contracts\CurrentUserReader;
use App\Domain\Auth\UserAccount;

final class GetCurrentUser
{
    public function __construct(
        private readonly CurrentUserReader $currentUserReader,
    ) {}

    public function handle(): UserAccount
    {
        return $this->currentUserReader->current();
    }
}
