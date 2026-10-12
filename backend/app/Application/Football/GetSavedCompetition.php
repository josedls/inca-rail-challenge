<?php

namespace App\Application\Football;

use App\Application\Football\Contracts\FootballCatalogReader;
use App\Application\Football\Data\CompetitionData;

final class GetSavedCompetition
{
    public function __construct(
        private readonly FootballCatalogReader $catalogReader,
    ) {}

    public function handle(int $externalId): ?CompetitionData
    {
        return $this->catalogReader->competition($externalId);
    }

    public function needsImport(int $externalId): bool
    {
        return ! $this->catalogReader->competitionHasTeams($externalId);
    }
}
