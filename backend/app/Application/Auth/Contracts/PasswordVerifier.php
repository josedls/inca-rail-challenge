<?php

namespace App\Application\Auth\Contracts;

interface PasswordVerifier
{
    public function matches(string $plainPassword, string $passwordHash): bool;
}
