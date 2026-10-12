<?php

namespace App\Application\Football;

use App\Application\Football\Contracts\CompetitionSyncDispatcher;

final class RequestCompetitionSync
{
    public function __construct(
        private readonly CompetitionSyncDispatcher $syncDispatcher,
    ) {}

    public function handle(): void
    {
        $this->syncDispatcher->dispatch();
    }
}
