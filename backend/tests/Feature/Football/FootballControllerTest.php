<?php

namespace Tests\Feature\Football;

use App\Models\Football\Competition;
use App\Models\Football\Player;
use App\Models\Football\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Laravel\Passport\ClientRepository;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

final class FootballControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.football_data.token' => 'test-token',
        ]);
    }

    public function test_it_lists_competitions_for_authorized_users(): void
    {
        Competition::query()->create([
            'external_id' => 2021,
            'name' => 'Premier League',
            'code' => 'PL',
        ]);
        $token = $this->tokenWithPermission('competitions.view');

        $this->withToken($token)
            ->getJson('/competitions')
            ->assertOk()
            ->assertJsonPath('data.0.id', 2021)
            ->assertJsonPath('data.0.name', 'Premier League');
    }

    public function test_it_lists_teams_for_authorized_users(): void
    {
        Team::query()->create([
            'external_id' => 64,
            'name' => 'Liverpool FC',
            'short_name' => 'Liverpool',
        ]);
        $token = $this->tokenWithPermission('team.view');

        $this->withToken($token)
            ->getJson('/team')
            ->assertOk()
            ->assertJsonPath('data.0.id', 64)
            ->assertJsonPath('data.0.short_name', 'Liverpool');
    }

    public function test_it_lists_players_for_authorized_users(): void
    {
        Player::query()->create([
            'external_id' => 117,
            'name' => 'Mohamed Salah',
            'position' => 'Right Winger',
            'shirt_number' => 11,
            'date_of_birth' => '1992-06-15',
            'nationality' => 'Egypt',
        ]);
        $token = $this->tokenWithPermission('players.view');

        $this->withToken($token)
            ->getJson('/players')
            ->assertOk()
            ->assertJsonPath('data.0.id', 117)
            ->assertJsonPath('data.0.shirt_number', 11)
            ->assertJsonPath('data.0.date_of_birth', '1992-06-15');
    }

    public function test_it_imports_a_competition_only_once(): void
    {
        Http::fake([
            'https://api.football-data.org/v4/competitions/2021' => Http::response([
                'id' => 2021,
                'name' => 'Premier League',
                'code' => 'PL',
                'emblem' => null,
            ]),
            'https://api.football-data.org/v4/competitions/2021/teams' => Http::response([
                'teams' => [
                    [
                        'id' => 64,
                        'name' => 'Liverpool FC',
                        'shortName' => 'Liverpool',
                        'crest' => null,
                    ],
                ],
            ]),
            'https://api.football-data.org/v4/teams/64' => Http::response([
                'squad' => [
                    [
                        'id' => 117,
                        'name' => 'Mohamed Salah',
                        'position' => 'Right Winger',
                        'shirtNumber' => 11,
                        'dateOfBirth' => '1992-06-15',
                        'nationality' => 'Egypt',
                    ],
                ],
            ]),
        ]);
        $token = $this->tokenWithPermission('competitions.view');

        $this->withToken($token)
            ->getJson('/competitions/2021')
            ->assertOk()
            ->assertJsonPath('data.id', 2021);

        $this->withToken($token)
            ->getJson('/competitions/2021')
            ->assertOk();

        self::assertDatabaseCount('football_competitions', 1);
        self::assertDatabaseCount('football_teams', 1);
        self::assertDatabaseCount('football_players', 1);
        self::assertDatabaseCount('competition_team', 1);
        self::assertDatabaseCount('team_player', 1);
        Http::assertSentCount(3);
    }

    public function test_it_rejects_unauthenticated_requests(): void
    {
        $this->getJson('/competitions')
            ->assertUnauthorized();
    }

    public function test_it_rejects_users_without_the_required_permission(): void
    {
        $token = $this->tokenWithPermission('competitions.view');

        $this->withToken($token)
            ->getJson('/players')
            ->assertForbidden();
    }

    private function tokenWithPermission(string $permission): string
    {
        Permission::findOrCreate($permission, 'api');

        $user = User::factory()->create();
        $role = Role::create([
            'name' => "role-{$permission}",
            'guard_name' => 'api',
        ]);
        $role->givePermissionTo($permission);
        $user->assignRole($role);

        app(ClientRepository::class)->createPersonalAccessGrantClient(
            'Inca Rail Football Test Client',
            'users',
        );

        return $user->createToken('football-test')->accessToken;
    }
}
