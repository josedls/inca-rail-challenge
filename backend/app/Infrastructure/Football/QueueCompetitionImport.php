<?php

namespace App\Infrastructure\Football;

use App\Application\Football\Contracts\CompetitionImportDispatcher;
use App\Jobs\Football\ImportCompetitionJob;

final class QueueCompetitionImport implements CompetitionImportDispatcher
{
    public function dispatch(int $competitionId): void
    {
        ImportCompetitionJob::dispatch($competitionId);
    }
}
