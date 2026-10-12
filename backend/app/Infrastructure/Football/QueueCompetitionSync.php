<?php

namespace App\Infrastructure\Football;

use App\Application\Football\Contracts\CompetitionSyncDispatcher;
use App\Jobs\Football\SyncCompetitionsJob;

final class QueueCompetitionSync implements CompetitionSyncDispatcher
{
    public function dispatch(): void
    {
        SyncCompetitionsJob::dispatch();
    }
}
