<?php

namespace Tests\Feature\Roles;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\ClientRepository;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

final class RoleCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_lists_roles_for_authorized_users(): void
    {
        $admin = $this->createUserWithPermission('roles.view');
        $permission = Permission::findOrCreate('users.view', 'api');
        $role = Role::create(['name' => 'report-reader', 'guard_name' => 'api']);
        $role->givePermissionTo($permission);
        $token = $this->createAccessToken($admin);

        $this->withToken($token)
            ->getJson('/roles')
            ->assertOk()
            ->assertJsonFragment([
                'name' => 'report-reader',
                'guard_name' => 'api',
                'permissions' => ['users.view'],
            ]);
    }

    public function test_it_creates_a_role_with_permissions(): void
    {
        $admin = $this->createUserWithPermission('roles.create');
        Permission::findOrCreate('users.view', 'api');
        $token = $this->createAccessToken($admin);

        $this->withToken($token)
            ->postJson('/roles', [
                'name' => 'operations-manager',
                'permissions' => ['users.view'],
            ])
            ->assertCreated()
            ->assertJsonPath('data.name', 'operations-manager')
            ->assertJsonPath('data.permissions', ['users.view']);

        $this->assertDatabaseHas('roles', [
            'name' => 'operations-manager',
            'guard_name' => 'api',
        ]);
    }

    public function test_it_updates_a_role_and_syncs_permissions(): void
    {
        $admin = $this->createUserWithPermission('roles.update');
        Permission::findOrCreate('users.view', 'api');
        Permission::findOrCreate('users.create', 'api');
        $role = Role::create(['name' => 'old-role', 'guard_name' => 'api']);
        $role->givePermissionTo('users.view');
        $token = $this->createAccessToken($admin);

        $this->withToken($token)
            ->patchJson('/roles/'.$role->id, [
                'name' => 'new-role',
                'permissions' => ['users.create'],
            ])
            ->assertOk()
            ->assertJsonPath('data.name', 'new-role')
            ->assertJsonPath('data.permissions', ['users.create']);

        $this->assertDatabaseHas('roles', [
            'id' => $role->id,
            'name' => 'new-role',
        ]);
    }

    public function test_it_deletes_a_role(): void
    {
        $admin = $this->createUserWithPermission('roles.delete');
        $role = Role::create(['name' => 'temporary-role', 'guard_name' => 'api']);
        $token = $this->createAccessToken($admin);

        $this->withToken($token)
            ->deleteJson('/roles/'.$role->id)
            ->assertNoContent();

        $this->assertDatabaseMissing('roles', ['id' => $role->id]);
    }

    public function test_it_rejects_users_without_role_permission(): void
    {
        $viewer = User::factory()->create([
            'name' => 'Inca Rail Viewer',
            'email' => 'viewer@inca-rail.test',
        ]);
        $token = $this->createAccessToken($viewer);

        $this->withToken($token)
            ->getJson('/roles')
            ->assertForbidden();
    }

    private function createUserWithPermission(string $permission): User
    {
        $user = User::factory()->create([
            'name' => 'Inca Rail Administrator',
            'email' => fake()->unique()->safeEmail(),
        ]);

        Permission::findOrCreate($permission, 'api');
        $role = Role::findOrCreate('admin-'.$permission, 'api');
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

        return $user->createToken('role-crud-test')->accessToken;
    }
}
