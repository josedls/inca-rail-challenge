<?php

namespace Tests\Unit\Application\Football;

use App\Application\Football\Contracts\CompetitionSyncDispatcher;
use App\Application\Football\RequestCompetitionSync;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;

final class RequestCompetitionSyncTest extends TestCase
{
    public function test_it_dispatches_the_competition_sync(): void
    {
        $dispatcher = $this->dispatcherMock();
        $dispatcher->expects(self::once())->method('dispatch');

        (new RequestCompetitionSync($dispatcher))->handle();
    }

    private function dispatcherMock(): MockObject&CompetitionSyncDispatcher
    {
        return $this->createMock(CompetitionSyncDispatcher::class);
    }
}
