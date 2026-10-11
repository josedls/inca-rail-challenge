<?php

namespace App\Domain\Football;

use RuntimeException;
use Throwable;

final class FootballDataApiException extends RuntimeException
{
    public function __construct(
        string $message,
        public readonly int $statusCode,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, $statusCode, $previous);
    }
}
