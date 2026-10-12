<?php

namespace App\Application\Football\Contracts;

interface CompetitionImportDispatcher
{
    public function dispatch(int $competitionId): void;
}
