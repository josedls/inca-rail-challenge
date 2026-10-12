<?php

namespace Tests\Unit\Infrastructure\Football;

use App\Application\Football\Data\CompetitionData;
use App\Application\Football\Data\PlayerData;
use App\Application\Football\Data\TeamData;
use App\Domain\Football\FootballDataApiException;
use App\Infrastructure\Football\FootballDataClient;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

final class FootballDataClientTest extends TestCase
{
    public function test_it_maps_competitions(): void
    {
        Http::fake([
            'https://api.football-data.test/v4/competitions' => Http::response([
                'competitions' => [
                    [
                        'id' => 2021,
                        'name' => 'Premier League',
                        'code' => 'PL',
                        'emblem' => 'https://example.test/pl.png',
                    ],
                ],
            ]),
        ]);

        $competitions = $this->client()->competitions();

        self::assertEquals([
            new CompetitionData(
                2021,
                'Premier League',
                'PL',
                'https://example.test/pl.png',
            ),
        ], $competitions);
    }

    public function test_it_maps_teams_and_squad_players(): void
    {
        Http::fake([
            'https://api.football-data.test/v4/competitions/2021/teams' => Http::response([
                'teams' => [
                    [
                        'id' => 64,
                        'name' => 'Liverpool FC',
                        'shortName' => 'Liverpool',
                        'crest' => 'https://example.test/liverpool.png',
                    ],
                ],
            ]),
            'https://api.football-data.test/v4/teams/64' => Http::response([
                'squad' => [
                    [
                        'id' => 117,
                        'name' => 'Mohamed Salah',
                        'position' => 'Right Winger',
                        'dateOfBirth' => '1992-06-15',
                        'nationality' => 'Egypt',
                    ],
                ],
            ]),
        ]);

        $client = $this->client();

        self::assertEquals([
            new TeamData(
                64,
                'Liverpool FC',
                'Liverpool',
                'https://example.test/liverpool.png',
            ),
        ], $client->teams(2021));

        self::assertEquals([
            new PlayerData(
                117,
                'Mohamed Salah',
                'Right Winger',
                '1992-06-15',
                'Egypt',
            ),
        ], $client->players(64));
    }

    public function test_it_sends_the_api_token_header(): void
    {
        Http::fake([
            'https://api.football-data.test/v4/competitions' => Http::response([
                'competitions' => [],
            ]),
        ]);

        $this->client('test-token')->competitions();

        Http::assertSent(function (Request $request): bool {
            return $request->hasHeader('X-Auth-Token', 'test-token');
        });
    }

    public function test_it_maps_rate_limit_errors(): void
    {
        Http::fake([
            'https://api.football-data.test/v4/competitions' => Http::response([], 429),
        ]);

        $this->expectException(FootballDataApiException::class);
        $this->expectExceptionMessage('Football data rate limit exceeded.');

        try {
            $this->client()->competitions();
        } catch (FootballDataApiException $exception) {
            self::assertSame(429, $exception->statusCode);

            throw $exception;
        }
    }

    public function test_it_maps_unavailable_service_errors(): void
    {
        Http::fake([
            'https://api.football-data.test/v4/competitions' => Http::response([], 503),
        ]);

        $this->expectException(FootballDataApiException::class);
        $this->expectExceptionMessage('Football data service is unavailable.');

        try {
            $this->client()->competitions();
        } catch (FootballDataApiException $exception) {
            self::assertSame(503, $exception->statusCode);

            throw $exception;
        }
    }

    public function test_it_rejects_requests_when_the_api_token_is_not_configured(): void
    {
        Http::fake();

        $this->expectException(FootballDataApiException::class);
        $this->expectExceptionMessage('Football data integration is not configured.');

        try {
            $this->client(null)->competitions();
        } catch (FootballDataApiException $exception) {
            self::assertSame(503, $exception->statusCode);
            Http::assertNothingSent();

            throw $exception;
        }
    }

    private function client(?string $token = 'test-token'): FootballDataClient
    {
        return new FootballDataClient(
            Http::baseUrl('https://api.football-data.test/v4')
                ->acceptJson()
                ->withHeaders([
                    'X-Auth-Token' => $token,
                ]),
            $token,
        );
    }
}
