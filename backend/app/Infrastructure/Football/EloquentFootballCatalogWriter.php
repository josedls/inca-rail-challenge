<?php

namespace App\Infrastructure\Football;

use App\Application\Football\Contracts\FootballCatalogWriter;
use App\Application\Football\Data\CompetitionData;
use App\Application\Football\Data\PlayerData;
use App\Application\Football\Data\TeamData;
use App\Domain\Football\CompetitionImportStatus;
use App\Models\Football\Competition;
use App\Models\Football\Player;
use App\Models\Football\Team;
use Illuminate\Support\Facades\DB;

final class EloquentFootballCatalogWriter implements FootballCatalogWriter
{
    public function saveCompetitions(array $competitions): void
    {
        foreach ($competitions as $competition) {
            $this->saveCompetition($competition);
        }
    }

    public function saveTeams(int $competitionId, array $teams): void
    {
        $competition = Competition::query()
            ->where('external_id', $competitionId)
            ->firstOrFail();

        DB::transaction(function () use ($competition, $teams): void {
            foreach ($teams as $team) {
                $savedTeam = $this->saveTeam($team);
                $competition->teams()->syncWithoutDetaching($savedTeam->id);
            }
        });
    }

    public function savePlayers(int $teamId, array $players): void
    {
        $team = Team::query()
            ->where('external_id', $teamId)
            ->firstOrFail();

        DB::transaction(function () use ($team, $players): void {
            foreach ($players as $player) {
                $savedPlayer = $this->savePlayer($player);
                $team->players()->syncWithoutDetaching($savedPlayer->id);
            }
        });
    }

    private function saveCompetition(CompetitionData $competition): Competition
    {
        return Competition::query()->updateOrCreate(
            ['external_id' => $competition->id],
            [
                'name' => $competition->name,
                'code' => $competition->code,
                'emblem' => $competition->emblem,
                'last_synced_at' => now(),
            ],
        );
    }

    public function markCompetitionProcessing(int $competitionId): void
    {
        Competition::query()
            ->where('external_id', $competitionId)
            ->update([
                'import_status' => CompetitionImportStatus::Processing->value,
                'import_error' => null,
                'failed_player_imports' => 0,
                'import_started_at' => now(),
                'import_completed_at' => null,
            ]);
    }

    public function setPendingPlayerImports(int $competitionId, int $pendingImports): void
    {
        Competition::query()
            ->where('external_id', $competitionId)
            ->update([
                'pending_player_imports' => $pendingImports,
                'failed_player_imports' => 0,
                'import_status' => $pendingImports === 0
                    ? CompetitionImportStatus::Completed->value
                    : CompetitionImportStatus::Processing->value,
                'import_completed_at' => $pendingImports === 0 ? now() : null,
            ]);
    }

    public function markPlayerImportCompleted(int $competitionId): void
    {
        DB::transaction(function () use ($competitionId): void {
            $competition = Competition::query()
                ->where('external_id', $competitionId)
                ->lockForUpdate()
                ->first();

            if ($competition === null || $competition->pending_player_imports === 0) {
                return;
            }

            $pendingImports = $competition->pending_player_imports - 1;
            $competition->update([
                'pending_player_imports' => $pendingImports,
                'import_status' => $pendingImports === 0
                    ? $this->finalImportStatus($competition->failed_player_imports)
                    : CompetitionImportStatus::Processing->value,
                'import_completed_at' => $pendingImports === 0 ? now() : null,
            ]);
        });
    }

    public function markPlayerImportFailed(int $competitionId, string $message): void
    {
        DB::transaction(function () use ($competitionId, $message): void {
            $competition = Competition::query()
                ->where('external_id', $competitionId)
                ->lockForUpdate()
                ->first();

            if ($competition === null || $competition->pending_player_imports === 0) {
                return;
            }

            $pendingImports = $competition->pending_player_imports - 1;
            $failedImports = $competition->failed_player_imports + 1;

            $competition->update([
                'pending_player_imports' => $pendingImports,
                'failed_player_imports' => $failedImports,
                'import_error' => $message,
                'import_status' => $pendingImports === 0
                    ? CompetitionImportStatus::Failed->value
                    : CompetitionImportStatus::Processing->value,
                'import_completed_at' => $pendingImports === 0 ? now() : null,
            ]);
        });
    }

    public function markCompetitionFailed(int $competitionId, string $message): void
    {
        Competition::query()
            ->where('external_id', $competitionId)
            ->update([
                'import_status' => CompetitionImportStatus::Failed->value,
                'import_error' => $message,
                'import_completed_at' => now(),
            ]);
    }

    private function finalImportStatus(int $failedImports): string
    {
        return $failedImports === 0
            ? CompetitionImportStatus::Completed->value
            : CompetitionImportStatus::Failed->value;
    }

    private function saveTeam(TeamData $team): Team
    {
        return Team::query()->updateOrCreate(
            ['external_id' => $team->id],
            [
                'name' => $team->name,
                'short_name' => $team->shortName,
                'crest' => $team->crest,
                'last_synced_at' => now(),
            ],
        );
    }

    private function savePlayer(PlayerData $player): Player
    {
        return Player::query()->updateOrCreate(
            ['external_id' => $player->id],
            [
                'name' => $player->name,
                'position' => $player->position,
                'shirt_number' => $player->shirtNumber,
                'date_of_birth' => $player->dateOfBirth,
                'nationality' => $player->nationality,
                'last_synced_at' => now(),
            ],
        );
    }
}
