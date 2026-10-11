<?php

namespace Tests\Unit\Application\Auth;

use App\Application\Auth\Contracts\TokenRevoker;
use App\Application\Auth\LogoutUser;
use PHPUnit\Framework\TestCase;

final class LogoutUserTest extends TestCase
{
    public function test_it_revokes_the_current_token(): void
    {
        $tokenRevoker = $this->createMock(TokenRevoker::class);
        $tokenRevoker->expects($this->once())->method('revoke');

        (new LogoutUser($tokenRevoker))->handle();

        $this->addToAssertionCount(1);
    }
}
