<?php

namespace App\Application\Football\Contracts;

interface CompetitionSyncDispatcher
{
    public function dispatch(): void;
}
