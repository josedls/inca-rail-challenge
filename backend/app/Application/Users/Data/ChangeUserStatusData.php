<?php

namespace App\Application\Users\Data;

final readonly class ChangeUserStatusData
{
    public function __construct(
        public bool $isActive,
    ) {}
}
