<?php

namespace App\Application\Football\Contracts;

interface FootballDataProvider
{
    public function competitions(): array;

    public function teams(int $competitionId): array;

    public function players(int $teamId): array;
}
