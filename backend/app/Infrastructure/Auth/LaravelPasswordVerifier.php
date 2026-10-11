<?php

namespace App\Infrastructure\Auth;

use App\Application\Auth\Contracts\PasswordVerifier;
use Illuminate\Support\Facades\Hash;

final class LaravelPasswordVerifier implements PasswordVerifier
{
    public function matches(string $plainPassword, string $passwordHash): bool
    {
        return Hash::check($plainPassword, $passwordHash);
    }
}
