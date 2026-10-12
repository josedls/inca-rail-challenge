<?php

namespace App\Application\Football;

use App\Application\Football\Contracts\FootballCatalogReader;

final class ListSavedPlayers
{
    public function __construct(
        private readonly FootballCatalogReader $catalogReader,
    ) {}

    public function handle(): array
    {
        return $this->catalogReader->players();
    }
}
