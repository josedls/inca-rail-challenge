<?php

namespace App\Application\Football;

use App\Application\Football\Contracts\CompetitionImportDispatcher;
use App\Application\Football\Contracts\FootballCatalogWriter;
use App\Application\Football\Data\CompetitionImportResult;
use App\Domain\Football\CompetitionImportStatus;

final class RequestCompetitionImport
{
    public function __construct(
        private readonly GetSavedCompetition $getSavedCompetition,
        private readonly FootballCatalogWriter $catalogWriter,
        private readonly CompetitionImportDispatcher $importDispatcher,
    ) {}

    public function handle(int $competitionId): CompetitionImportResult
    {
        $competition = $this->getSavedCompetition->handle($competitionId);
        $status = $competition?->importStatus;

        if ($status === CompetitionImportStatus::Processing) {
            return new CompetitionImportResult($competitionId, $status->value, 202);
        }

        if ($status === CompetitionImportStatus::Completed) {
            return new CompetitionImportResult($competitionId, $status->value, 200);
        }

        $this->catalogWriter->markCompetitionProcessing($competitionId);
        $this->importDispatcher->dispatch($competitionId);

        return new CompetitionImportResult($competitionId, 'queued', 202);
    }
}
