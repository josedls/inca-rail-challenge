<?php

namespace App\Application\Users;

use App\Application\Users\Contracts\UserCreator;
use App\Application\Users\Data\CreateUserData;

final class CreateUser
{
    public function __construct(
        private readonly UserCreator $userCreator,
    ) {}

    public function handle(CreateUserData $data): UserSummary
    {
        return $this->userCreator->create($data);
    }
}
