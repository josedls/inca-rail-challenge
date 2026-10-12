<?php

namespace App\Application\Football;

use App\Application\Football\Contracts\FootballCatalogWriter;
use App\Application\Football\Contracts\FootballDataProvider;

final class ImportCompetition
{
    public function __construct(
        private readonly FootballDataProvider $footballDataProvider,
        private readonly FootballCatalogWriter $catalogWriter,
    ) {}

    public function handle(int $competitionId): void
    {
        $competition = $this->footballDataProvider->competition($competitionId);
        $this->catalogWriter->saveCompetitions([$competition]);

        $teams = $this->footballDataProvider->teams($competitionId);
        $this->catalogWriter->saveTeams($competitionId, $teams);

        foreach ($teams as $team) {
            $players = $this->footballDataProvider->players($team->id);
            $this->catalogWriter->savePlayers($team->id, $players);
        }
    }
}
