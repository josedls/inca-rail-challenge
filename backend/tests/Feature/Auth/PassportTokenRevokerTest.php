<?php

namespace Tests\Feature\Auth;

use App\Infrastructure\Auth\PassportTokenRevoker;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Passport\AccessToken;
use Laravel\Passport\Passport;
use Tests\TestCase;

final class PassportTokenRevokerTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_revokes_a_persisted_passport_access_token(): void
    {
        $user = User::factory()->create([
            'name' => 'Valeria Quispe',
            'email' => 'valeria.quispe@inca-rail.test',
        ]);

        $client = Passport::client();
        $client->forceFill([
            'id' => (string) Str::uuid(),
            'name' => 'Inca Rail Test Client',
            'secret' => null,
            'provider' => 'users',
            'redirect_uris' => json_encode([]),
            'grant_types' => json_encode(['personal_access']),
            'revoked' => false,
        ])->save();

        $token = Passport::token();
        $token->forceFill([
            'id' => Str::random(80),
            'user_id' => $user->id,
            'client_id' => $client->id,
            'name' => 'logout-test-token',
            'scopes' => json_encode([]),
            'revoked' => false,
            'expires_at' => now()->addHour(),
        ])->save();

        Auth::guard('api')->setUser($user->withAccessToken(
            new AccessToken(['oauth_access_token_id' => $token->id]),
        ));

        app(PassportTokenRevoker::class)->revoke();

        $this->assertTrue(Passport::token()->newQuery()->findOrFail($token->id)->revoked);
    }
}
