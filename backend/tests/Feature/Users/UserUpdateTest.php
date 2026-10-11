<?php

namespace Tests\Feature\Users;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\ClientRepository;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

final class UserUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_updates_user_data_and_roles(): void
    {
        $admin = $this->createUserWithPermission('users.update');
        $user = User::factory()->create([
            'name' => 'Old Name',
            'email' => 'old.name@inca-rail.test',
        ]);
        Role::findOrCreate('viewer', 'api');
        $user->assignRole('viewer');

        $token = $this->createAccessToken($admin);

        $response = $this->withToken($token)->patchJson('/users/'.$user->id, [
            'name' => 'Valeria Quispe',
            'email' => 'valeria.quispe@inca-rail.test',
            'password' => 'Railway#2026',
            'password_confirmation' => 'Railway#2026',
            'roles' => ['admin'],
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('data.name', 'Valeria Quispe')
            ->assertJsonPath('data.email', 'valeria.quispe@inca-rail.test')
            ->assertJsonPath('data.roles', ['admin']);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'email' => 'valeria.quispe@inca-rail.test',
        ]);
        $this->assertTrue(password_verify('Railway#2026', $user->fresh()->password));
    }

    public function test_it_rejects_users_without_users_update_permission(): void
    {
        $viewer = User::factory()->create([
            'name' => 'Inca Rail Viewer',
            'email' => 'viewer@inca-rail.test',
        ]);
        $user = User::factory()->create();
        $token = $this->createAccessToken($viewer);

        $this->withToken($token)
            ->patchJson('/users/'.$user->id, [
                'name' => 'Updated Name',
                'email' => 'updated@inca-rail.test',
            ])
            ->assertForbidden();
    }

    public function test_it_validates_user_update_data(): void
    {
        $admin = $this->createUserWithPermission('users.update');
        $user = User::factory()->create();
        $token = $this->createAccessToken($admin);

        $this->withToken($token)
            ->patchJson('/users/'.$user->id, [
                'name' => '',
                'email' => 'invalid-email',
                'password' => 'short',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'email', 'password']);
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

        return $user->createToken('user-update-test')->accessToken;
    }
}
