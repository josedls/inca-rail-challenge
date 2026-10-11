<?php

namespace Tests\Feature\Users;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\ClientRepository;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

final class UserStatusTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_deactivates_a_user_and_blocks_their_access(): void
    {
        $admin = $this->createUserWithPermission('users.update');
        $user = $this->createUserWithPermission('users.view');
        $adminToken = $this->createAccessToken($admin);
        $userToken = $this->createAccessToken($user);

        $this->withToken($adminToken)
            ->patchJson('/users/'.$user->id.'/status', ['is_active' => false])
            ->assertOk()
            ->assertJsonPath('data.is_active', false);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'is_active' => false,
        ]);

        $this->withToken($userToken)
            ->getJson('/users')
            ->assertForbidden();
    }

    public function test_it_reactivates_a_user(): void
    {
        $admin = $this->createUserWithPermission('users.update');
        $user = $this->createUserWithPermission('users.view', false);
        $adminToken = $this->createAccessToken($admin);

        $this->withToken($adminToken)
            ->patchJson('/users/'.$user->id.'/status', ['is_active' => true])
            ->assertOk()
            ->assertJsonPath('data.is_active', true);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'is_active' => true,
        ]);
    }

    public function test_it_rejects_users_without_users_update_permission(): void
    {
        $viewer = $this->createUserWithPermission('users.view');
        $user = User::factory()->create();
        $token = $this->createAccessToken($viewer);

        $this->withToken($token)
            ->patchJson('/users/'.$user->id.'/status', ['is_active' => false])
            ->assertForbidden();
    }

    private function createUserWithPermission(string $permission, bool $isActive = true): User
    {
        $user = User::factory()->create([
            'name' => 'Inca Rail Test User',
            'email' => fake()->unique()->safeEmail(),
            'is_active' => $isActive,
        ]);

        Permission::findOrCreate($permission, 'api');
        $role = Role::findOrCreate('test-role-'.$permission, 'api');
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

        return $user->createToken('user-status-test')->accessToken;
    }
}
