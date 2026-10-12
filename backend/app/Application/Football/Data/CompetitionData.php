<?php

namespace App\Application\Football\Data;

final readonly class CompetitionData
{
    public function __construct(
        public int $id,
        public string $name,
        public ?string $code,
        public ?string $emblem,
        public ?string $importStatus = null,
        public ?string $importError = null,
        public ?string $lastSyncedAt = null,
    ) {}
}
