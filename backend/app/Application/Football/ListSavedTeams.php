<?php

namespace App\Application\Football;

use App\Application\Football\Contracts\FootballCatalogReader;

final class ListSavedTeams
{
    public function __construct(
        private readonly FootballCatalogReader $catalogReader,
    ) {}

    public function handle(): array
    {
        return $this->catalogReader->teams();
    }
}
