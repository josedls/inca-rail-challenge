<?php

namespace App\Application\Football\Contracts;

use App\Application\Football\Data\CompetitionData;

interface FootballDataProvider
{
    public function competitions(): array;

    public function competition(int $competitionId): CompetitionData;

    public function teams(int $competitionId): array;

    public function players(int $teamId): array;
}
