<?php

namespace App\Application\Football\Data;

final readonly class CompetitionImportResult
{
    public function __construct(
        public int $competitionId,
        public string $status,
        public int $httpStatus,
    ) {}

    public function toArray(): array
    {
        return [
            'competition_id' => $this->competitionId,
            'status' => $this->status,
        ];
    }
}
