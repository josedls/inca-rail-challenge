<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\ClientRepository;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

final class MeTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_returns_the_authenticated_user_roles_and_permissions(): void
    {
        $user = User::factory()->create([
            'name' => 'Valeria Quispe',
            'email' => 'valeria.quispe@inca-rail.test',
        ]);
        Permission::findOrCreate('roles.view', 'api');
        Permission::findOrCreate('users.view', 'api');
        $role = Role::create(['name' => 'viewer', 'guard_name' => 'api']);
        $role->givePermissionTo(['roles.view', 'users.view']);
        $user->assignRole($role);
        $token = $this->createAccessToken($user);

        $response = $this->withToken($token)->getJson('/me');

        $response->assertOk()
            ->assertJsonPath('data.id', $user->id)
            ->assertJsonPath('data.name', 'Valeria Quispe')
            ->assertJsonPath('data.roles', ['viewer']);

        $this->assertEqualsCanonicalizing(
            ['roles.view', 'users.view'],
            $response->json('data.permissions'),
        );
    }

    public function test_it_rejects_unauthenticated_requests(): void
    {
        $this->getJson('/me')->assertUnauthorized();
    }

    private function createAccessToken(User $user): string
    {
        app(ClientRepository::class)->createPersonalAccessGrantClient(
            'Inca Rail Test Client',
            'users',
        );

        return $user->createToken('me-test')->accessToken;
    }
}
