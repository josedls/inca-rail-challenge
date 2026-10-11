<?php

namespace Tests\Feature\Http;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\ClientRepository;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

final class ApiErrorResponseTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_returns_a_consistent_401_response(): void
    {
        $this->getJson('/me')
            ->assertUnauthorized()
            ->assertJson([
                'message' => 'Unauthenticated.',
            ]);
    }

    public function test_it_returns_a_consistent_403_response(): void
    {
        $user = User::factory()->create();
        $token = $this->createAccessToken($user);

        $this->withToken($token)
            ->getJson('/roles')
            ->assertForbidden()
            ->assertJson([
                'message' => 'Forbidden.',
            ]);
    }

    public function test_it_returns_a_consistent_422_response(): void
    {
        $this->postJson('/login', [])
            ->assertUnprocessable()
            ->assertJsonStructure([
                'message',
                'errors' => ['email', 'password'],
            ]);
    }

    public function test_it_returns_a_consistent_404_response(): void
    {
        $user = User::factory()->create();
        Permission::findOrCreate('roles.update', 'api');
        $role = Role::create(['name' => 'role-manager', 'guard_name' => 'api']);
        $role->givePermissionTo('roles.update');
        $user->assignRole($role);
        $token = $this->createAccessToken($user);

        $this->withToken($token)
            ->patchJson('/roles/999999', [
                'name' => 'updated-role',
                'permissions' => [],
            ])
            ->assertNotFound()
            ->assertJson([
                'message' => 'Resource not found.',
            ]);
    }

    private function createAccessToken(User $user): string
    {
        app(ClientRepository::class)->createPersonalAccessGrantClient(
            'Inca Rail Test Client',
            'users',
        );

        return $user->createToken('api-error-test')->accessToken;
    }
}
