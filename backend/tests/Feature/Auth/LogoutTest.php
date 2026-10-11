<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\ClientRepository;
use Laravel\Passport\Passport;
use Tests\TestCase;

final class LogoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_rejects_unauthenticated_requests(): void
    {
        $this->postJson('/logout')->assertUnauthorized();
    }

    public function test_it_revokes_the_authenticated_passport_token(): void
    {
        $user = User::factory()->create([
            'name' => 'Valeria Quispe',
            'email' => 'valeria.quispe@inca-rail.test',
        ]);

        app(ClientRepository::class)->createPersonalAccessGrantClient(
            'Inca Rail Test Client',
            'users',
        );

        $token = $user->createToken('logout-test');

        $response = $this->withToken($token->accessToken)->postJson('/logout');

        $response->assertNoContent();
        $this->assertTrue(Passport::token()->newQuery()->findOrFail($token->accessTokenId)->revoked);
    }

    public function test_it_rejects_tokens_for_inactive_users(): void
    {
        $user = User::factory()->create([
            'name' => 'Valeria Quispe',
            'email' => 'valeria.quispe@inca-rail.test',
            'is_active' => false,
        ]);

        app(ClientRepository::class)->createPersonalAccessGrantClient(
            'Inca Rail Test Client',
            'users',
        );

        $token = $user->createToken('inactive-user-test');

        $this->withToken($token->accessToken)
            ->postJson('/logout')
            ->assertUnauthorized();

        $this->assertFalse(Passport::token()->newQuery()->findOrFail($token->accessTokenId)->revoked);
    }
}
