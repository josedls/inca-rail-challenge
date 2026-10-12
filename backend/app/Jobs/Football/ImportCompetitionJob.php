<?php

namespace App\Jobs\Football;

use App\Application\Football\Contracts\FootballCatalogWriter;
use App\Application\Football\Contracts\FootballDataProvider;
use App\Domain\Football\FootballDataApiException;
use App\Jobs\Football\Concerns\RespectsFootballDataRetryWindow;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\RateLimited;
use Illuminate\Queue\SerializesModels;
use Throwable;

final class ImportCompetitionJob implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    use RespectsFootballDataRetryWindow;

    public array $backoff = [60, 120, 300];

    public int $timeout = 120;

    public int $uniqueFor = 3600;

    public function __construct(
        public readonly int $competitionId,
    ) {}

    public function uniqueId(): string
    {
        return "football-competition:{$this->competitionId}";
    }

    public function middleware(): array
    {
        return [new RateLimited('football-data')];
    }

    public function handle(
        FootballDataProvider $footballDataProvider,
        FootballCatalogWriter $catalogWriter,
    ): void {
        try {
            $competition = $footballDataProvider->competition($this->competitionId);
            $catalogWriter->saveCompetitions([$competition]);
        } catch (FootballDataApiException $exception) {
            if ($exception->retryable) {
                throw $exception;
            }

            $catalogWriter->markCompetitionFailed(
                $this->competitionId,
                $exception->getMessage(),
            );

            return;
        }

        ImportCompetitionTeamsJob::dispatch($this->competitionId);
    }

    public function failed(?Throwable $exception): void
    {
        app(FootballCatalogWriter::class)->markCompetitionFailed(
            $this->competitionId,
            $exception?->getMessage() ?? 'Competition import failed.',
        );
    }
}
