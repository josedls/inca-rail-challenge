<?php

namespace App\Application\Football;

use App\Application\Football\Contracts\FootballDataProvider;

final class ListPlayers
{
    public function __construct(
        private readonly FootballDataProvider $footballDataProvider,
    ) {}

    public function handle(int $teamId): array
    {
        return $this->footballDataProvider->players($teamId);
    }
}
