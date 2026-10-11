<?php

namespace Tests\Feature\Permissions;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\ClientRepository;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

final class PermissionCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_lists_permissions_for_authorized_users(): void
    {
        $admin = $this->createUserWithPermission('permissions.view');
        Permission::create(['name' => 'reports.view', 'guard_name' => 'api']);
        $token = $this->createAccessToken($admin);

        $this->withToken($token)
            ->getJson('/permissions')
            ->assertOk()
            ->assertJsonFragment([
                'name' => 'reports.view',
                'guard_name' => 'api',
            ]);
    }

    public function test_it_creates_a_permission(): void
    {
        $admin = $this->createUserWithPermission('permissions.create');
        $token = $this->createAccessToken($admin);

        $this->withToken($token)
            ->postJson('/permissions', ['name' => 'reports.view'])
            ->assertCreated()
            ->assertJsonPath('data.name', 'reports.view')
            ->assertJsonPath('data.guard_name', 'api');

        $this->assertDatabaseHas('permissions', [
            'name' => 'reports.view',
            'guard_name' => 'api',
        ]);
    }

    public function test_it_updates_a_permission(): void
    {
        $admin = $this->createUserWithPermission('permissions.update');
        $permission = Permission::create([
            'name' => 'reports.view',
            'guard_name' => 'api',
        ]);
        $token = $this->createAccessToken($admin);

        $this->withToken($token)
            ->patchJson('/permissions/'.$permission->id, ['name' => 'reports.read'])
            ->assertOk()
            ->assertJsonPath('data.name', 'reports.read');

        $this->assertDatabaseHas('permissions', [
            'id' => $permission->id,
            'name' => 'reports.read',
        ]);
    }

    public function test_it_deletes_a_permission(): void
    {
        $admin = $this->createUserWithPermission('permissions.delete');
        $permission = Permission::create([
            'name' => 'reports.view',
            'guard_name' => 'api',
        ]);
        $token = $this->createAccessToken($admin);

        $this->withToken($token)
            ->deleteJson('/permissions/'.$permission->id)
            ->assertNoContent();

        $this->assertDatabaseMissing('permissions', ['id' => $permission->id]);
    }

    public function test_it_rejects_users_without_permission_management_permission(): void
    {
        $viewer = User::factory()->create([
            'name' => 'Inca Rail Viewer',
            'email' => 'viewer@inca-rail.test',
        ]);
        $token = $this->createAccessToken($viewer);

        $this->withToken($token)
            ->getJson('/permissions')
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

        return $user->createToken('permission-crud-test')->accessToken;
    }
}
