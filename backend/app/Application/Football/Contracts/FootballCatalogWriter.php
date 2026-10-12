<?php

namespace App\Application\Football\Contracts;

interface FootballCatalogWriter
{
    public function saveCompetitions(array $competitions): void;

    public function saveTeams(int $competitionId, array $teams): void;

    public function savePlayers(int $teamId, array $players): void;
}
