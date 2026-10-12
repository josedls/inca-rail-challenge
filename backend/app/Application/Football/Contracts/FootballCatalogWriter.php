<?php

namespace App\Application\Football\Contracts;

interface FootballCatalogWriter
{
    public function saveCompetitions(array $competitions): void;

    public function saveTeams(int $competitionId, array $teams): void;

    public function savePlayers(int $teamId, array $players): void;

    public function markCompetitionProcessing(int $competitionId): void;

    public function setPendingPlayerImports(int $competitionId, int $pendingImports): void;

    public function markPlayerImportCompleted(int $competitionId): void;

    public function markPlayerImportFailed(int $competitionId, string $message): void;

    public function markCompetitionFailed(int $competitionId, string $message): void;
}
