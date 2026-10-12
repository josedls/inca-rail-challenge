<?php

namespace App\Application\Football\Contracts;

use App\Application\Football\Data\CompetitionData;
use App\Application\Football\Data\TeamData;

interface FootballCatalogReader
{
    public function competitions(): array;

    public function competition(int $externalId): ?CompetitionData;

    public function competitionHasTeams(int $externalId): bool;

    public function teams(): array;

    public function team(int $externalId): ?TeamData;

    public function players(): array;
}
