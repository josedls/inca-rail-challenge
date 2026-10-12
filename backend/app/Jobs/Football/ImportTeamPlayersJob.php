<?php

namespace App\Jobs\Football;

use App\Application\Football\Contracts\FootballCatalogWriter;
use App\Application\Football\Contracts\FootballDataProvider;
use App\Jobs\Football\Concerns\RespectsFootballDataRetryWindow;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\RateLimited;
use Illuminate\Queue\SerializesModels;
use Throwable;

final class ImportTeamPlayersJob implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    use RespectsFootballDataRetryWindow;

    public array $backoff = [60, 120, 300];

    public int $timeout = 120;

    public int $uniqueFor = 3600;

    public function __construct(
        public readonly int $competitionId,
        public readonly int $teamId,
    ) {}

    public function uniqueId(): string
    {
        return "football-team-players:{$this->competitionId}:{$this->teamId}";
    }

    public function middleware(): array
    {
        return [new RateLimited('football-data')];
    }

    public function handle(
        FootballDataProvider $footballDataProvider,
        FootballCatalogWriter $catalogWriter,
    ): void {
        $players = $footballDataProvider->players($this->teamId);
        $catalogWriter->savePlayers($this->teamId, $players);
        $catalogWriter->markPlayerImportCompleted($this->competitionId);
    }

    public function failed(?Throwable $exception): void
    {
        app(FootballCatalogWriter::class)->markPlayerImportFailed(
            $this->competitionId,
            $exception?->getMessage() ?? 'Player import failed.',
        );
    }
}
