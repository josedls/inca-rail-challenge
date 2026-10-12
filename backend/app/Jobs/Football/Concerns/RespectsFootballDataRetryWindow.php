<?php

namespace App\Jobs\Football\Concerns;

use DateTimeInterface;

trait RespectsFootballDataRetryWindow
{
    public int $tries = 0;

    public function retryUntil(): DateTimeInterface
    {
        return now()->addMinutes(
            (int) config('services.football_data.retry_window_minutes', 15),
        );
    }
}
