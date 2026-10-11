<?php

namespace Tests\Feature\Users;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\ClientRepository;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

final class UserIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_lists_users_for_users_view_permission(): void
    {
        $admin = $this->createUserWithPermission('users.view');
        $user = User::factory()->create([
            'name' => 'Valeria Quispe',
            'email' => 'valeria.quispe@inca-rail.test',
        ]);

        $token = $this->createAccessToken($admin);

        $response = $this->withToken($token)->getJson('/users');

        $response
            ->assertOk()
            ->assertJsonPath('data.0.email', $admin->email)
            ->assertJsonPath('data.1.email', $user->email)
            ->assertJsonPath('data.1.roles', []);
    }

    public function test_it_rejects_users_without_users_view_permission(): void
    {
        $viewer = User::factory()->create([
            'name' => 'Inca Rail Viewer',
            'email' => 'viewer@inca-rail.test',
        ]);

        $token = $this->createAccessToken($viewer);

        $response = $this->withToken($token)->getJson('/users');

        $response->assertForbidden();
    }

    private function createUserWithPermission(string $permission): User
    {
        $user = User::factory()->create([
            'name' => 'Inca Rail Administrator',
            'email' => 'admin@inca-rail.test',
        ]);

        Permission::findOrCreate($permission, 'api');
        $role = Role::findOrCreate('admin', 'api');
        $role->givePermissionTo($permission);
        $user->assignRole($role);

        return $user;
    }

    private function createAccessToken(User $user): string
    {
        app(ClientRepository::class)->createPersonalAccessGrantClient(
            'Inca Rail Test Client',
            'users',
        );

        return $user->createToken('user-index-test')->accessToken;
    }
}
