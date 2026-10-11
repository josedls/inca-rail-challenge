<?php

namespace App\Application\Football;

use App\Application\Football\Contracts\FootballDataProvider;

final class ListCompetitions
{
    public function __construct(
        private readonly FootballDataProvider $footballDataProvider,
    ) {}

    public function handle(): array
    {
        return $this->footballDataProvider->competitions();
    }
}
