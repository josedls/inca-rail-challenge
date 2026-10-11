<?php

namespace App\Application\Football;

use App\Application\Football\Contracts\FootballDataProvider;

final class ListTeams
{
    public function __construct(
        private readonly FootballDataProvider $footballDataProvider,
    ) {}

    public function handle(int $competitionId): array
    {
        return $this->footballDataProvider->teams($competitionId);
    }
}
