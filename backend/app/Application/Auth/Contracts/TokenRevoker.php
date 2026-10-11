<?php

namespace App\Application\Auth\Contracts;

interface TokenRevoker
{
    public function revoke(): void;
}
