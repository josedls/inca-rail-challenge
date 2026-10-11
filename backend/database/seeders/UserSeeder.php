<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use RuntimeException;

final class UserSeeder extends Seeder
{
    public function run(): void
    {
        $this->createUser(
            name: env('ADMIN_NAME', 'Inca Rail Administrator'),
            email: env('ADMIN_EMAIL', 'admin@inca-rail.test'),
            passwordKey: 'ADMIN_PASSWORD',
            role: 'admin',
        );

        $this->createUser(
            name: env('VIEWER_NAME', 'Inca Rail Viewer'),
            email: env('VIEWER_EMAIL', 'viewer@inca-rail.test'),
            passwordKey: 'VIEWER_PASSWORD',
            role: 'viewer',
        );
    }

    private function createUser(
        string $name,
        string $email,
        string $passwordKey,
        string $role,
    ): void {
        $password = env($passwordKey);

        if (! is_string($password) || $password === '') {
            throw new RuntimeException("The {$passwordKey} environment variable is required.");
        }

        $user = User::firstOrNew(['email' => $email]);
        $user->name = $name;

        if (! $user->exists) {
            $user->password = $password;
        }

        $user->save();
        $user->syncRoles($role);
    }
}
