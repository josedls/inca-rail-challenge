<?php

namespace Tests\Feature\Users;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\ClientRepository;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

final class UserCreateTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_a_user_with_roles(): void
    {
        $admin = $this->createUserWithPermission('users.create');
        Role::findOrCreate('viewer', 'api');
        $token = $this->createAccessToken($admin);

        $response = $this->withToken($token)->postJson('/users', [
            'name' => 'Valeria Quispe',
            'email' => 'valeria.quispe@inca-rail.test',
            'password' => 'Railway#2026',
            'password_confirmation' => 'Railway#2026',
            'roles' => ['viewer'],
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('data.email', 'valeria.quispe@inca-rail.test')
            ->assertJsonPath('data.is_active', true)
            ->assertJsonPath('data.roles', ['viewer']);

        $this->assertDatabaseHas('users', [
            'email' => 'valeria.quispe@inca-rail.test',
            'is_active' => true,
        ]);
    }

    public function test_it_rejects_users_without_users_create_permission(): void
    {
        $viewer = User::factory()->create([
            'name' => 'Inca Rail Viewer',
            'email' => 'viewer@inca-rail.test',
        ]);
        $token = $this->createAccessToken($viewer);

        $this->withToken($token)
            ->postJson('/users', [])
            ->assertForbidden();
    }

    public function test_it_validates_user_creation_data(): void
    {
        $admin = $this->createUserWithPermission('users.create');
        $token = $this->createAccessToken($admin);

        $this->withToken($token)
            ->postJson('/users', [
                'email' => 'invalid-email',
                'password' => 'short',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'email', 'password', 'roles']);
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

        return $user->createToken('user-create-test')->accessToken;
    }
}
