<?php

namespace App\Infrastructure\Football;

use App\Application\Football\Contracts\FootballCatalogReader;
use App\Application\Football\Data\CompetitionData;
use App\Application\Football\Data\PlayerData;
use App\Application\Football\Data\TeamData;
use App\Domain\Football\CompetitionImportStatus;
use App\Models\Football\Competition;
use App\Models\Football\Player;
use App\Models\Football\Team;

final class EloquentFootballCatalogReader implements FootballCatalogReader
{
    public function competitions(): array
    {
        return Competition::query()
            ->withCount('teams')
            ->orderBy('name')
            ->get()
            ->map(fn (Competition $competition): CompetitionData => $this->competitionData($competition))
            ->all();
    }

    public function competition(int $externalId): ?CompetitionData
    {
        $competition = Competition::query()
            ->where('external_id', $externalId)
            ->withCount('teams')
            ->first();

        return $competition === null ? null : $this->competitionData($competition);
    }

    public function teams(): array
    {
        return Team::query()
            ->orderBy('name')
            ->get()
            ->map(fn (Team $team): TeamData => $this->teamData($team))
            ->all();
    }

    public function team(int $externalId): ?TeamData
    {
        $team = Team::query()
            ->where('external_id', $externalId)
            ->first();

        return $team === null ? null : $this->teamData($team);
    }

    public function players(): array
    {
        return Player::query()
            ->orderBy('name')
            ->get()
            ->map(fn (Player $player): PlayerData => $this->playerData($player))
            ->all();
    }

    private function competitionData(Competition $competition): CompetitionData
    {
        return new CompetitionData(
            $competition->external_id,
            $competition->name,
            $competition->code,
            $competition->emblem,
            CompetitionImportStatus::tryFrom($competition->import_status),
            $competition->import_error,
            $competition->last_synced_at?->toIso8601String(),
            $competition->pending_player_imports,
            $competition->teams_count,
            $competition->failed_player_imports,
        );
    }

    private function teamData(Team $team): TeamData
    {
        return new TeamData(
            $team->external_id,
            $team->name,
            $team->short_name,
            $team->crest,
        );
    }

    private function playerData(Player $player): PlayerData
    {
        return new PlayerData(
            $player->external_id,
            $player->name,
            $player->position,
            $player->shirt_number,
            $player->date_of_birth?->toDateString(),
            $player->nationality,
        );
    }
}
