<?php

namespace App\Application\Football;

use App\Application\Football\Contracts\FootballCatalogReader;
use App\Application\Football\Contracts\FootballCatalogWriter;
use App\Application\Football\Contracts\FootballDataProvider;

final class ListSavedCompetitions
{
    public function __construct(
        private readonly FootballCatalogReader $catalogReader,
        private readonly FootballDataProvider $footballDataProvider,
        private readonly FootballCatalogWriter $catalogWriter,
    ) {}

    public function handle(): array
    {
        $competitions = $this->catalogReader->competitions();

        if ($competitions !== []) {
            return $competitions;
        }

        $competitions = $this->footballDataProvider->competitions();
        $this->catalogWriter->saveCompetitions($competitions);

        return $competitions;
    }
}
