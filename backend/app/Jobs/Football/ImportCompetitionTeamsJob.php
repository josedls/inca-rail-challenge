<?php

namespace App\Jobs\Football;

use App\Application\Football\Contracts\FootballCatalogWriter;
use App\Application\Football\Contracts\FootballDataProvider;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\RateLimited;
use Illuminate\Queue\SerializesModels;
use Throwable;

final class ImportCompetitionTeamsJob implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public array $backoff = [60, 120, 300];

    public int $timeout = 120;

    public int $uniqueFor = 3600;

    public function __construct(
        public readonly int $competitionId,
    ) {}

    public function uniqueId(): string
    {
        return "football-competition-teams:{$this->competitionId}";
    }

    public function middleware(): array
    {
        return [new RateLimited('football-data')];
    }

    public function handle(
        FootballDataProvider $footballDataProvider,
        FootballCatalogWriter $catalogWriter,
    ): void {
        $teams = $footballDataProvider->teams($this->competitionId);
        $catalogWriter->saveTeams($this->competitionId, $teams);
        $catalogWriter->setPendingPlayerImports($this->competitionId, count($teams));

        foreach ($teams as $team) {
            ImportTeamPlayersJob::dispatch($this->competitionId, $team->id);
        }
    }

    public function failed(?Throwable $exception): void
    {
        app(FootballCatalogWriter::class)->markCompetitionFailed(
            $this->competitionId,
            $exception?->getMessage() ?? 'Team import failed.',
        );
    }
}
