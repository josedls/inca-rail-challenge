<?php

namespace App\Application\Football\Data;

final readonly class PlayerData
{
    public function __construct(
        public int $id,
        public string $name,
        public ?string $position,
        public ?int $shirtNumber,
        public ?string $dateOfBirth,
        public ?string $nationality,
    ) {}
}
