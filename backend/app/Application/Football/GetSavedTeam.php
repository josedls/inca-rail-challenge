<?php

namespace App\Application\Football;

use App\Application\Football\Contracts\FootballCatalogReader;
use App\Application\Football\Data\TeamData;

final class GetSavedTeam
{
    public function __construct(
        private readonly FootballCatalogReader $catalogReader,
    ) {}

    public function handle(int $externalId): ?TeamData
    {
        return $this->catalogReader->team($externalId);
    }
}
