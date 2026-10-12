<?php

namespace App\Application\Football\Data;

final readonly class TeamData
{
    public function __construct(
        public int $id,
        public string $name,
        public ?string $shortName,
        public ?string $crest,
        public ?array $players = null,
    ) {}
}
