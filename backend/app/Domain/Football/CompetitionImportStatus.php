<?php

namespace App\Domain\Football;

enum CompetitionImportStatus: string
{
    case Available = 'available';
    case Processing = 'processing';
    case Completed = 'completed';
    case Failed = 'failed';
}
