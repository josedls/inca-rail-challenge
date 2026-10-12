<?php

namespace App\Domain\Football;

use RuntimeException;
use Throwable;

final class FootballDataApiException extends RuntimeException
{
    public function __construct(
        string $message,
        public readonly int $statusCode,
        public readonly bool $retryable,
        public readonly ?string $endpoint = null,
        public readonly ?int $upstreamStatusCode = null,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, $statusCode, $previous);
    }
}
