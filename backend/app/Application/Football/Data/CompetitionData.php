<?php

namespace App\Application\Football\Data;

use App\Domain\Football\CompetitionImportStatus;

final readonly class CompetitionData
{
    public function __construct(
        public int $id,
        public string $name,
        public ?string $code,
        public ?string $emblem,
        public ?CompetitionImportStatus $importStatus = null,
        public ?string $importError = null,
        public ?string $lastSyncedAt = null,
        public int $pendingPlayerImports = 0,
        public int $totalTeams = 0,
        public int $failedPlayerImports = 0,
        public ?array $teams = null,
    ) {}
}
