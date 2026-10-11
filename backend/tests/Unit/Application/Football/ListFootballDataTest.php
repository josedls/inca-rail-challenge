<?php

namespace Tests\Unit\Application\Football;

use App\Application\Football\Contracts\FootballDataProvider;
use App\Application\Football\Data\CompetitionData;
use App\Application\Football\Data\PlayerData;
use App\Application\Football\Data\TeamData;
use App\Application\Football\ListCompetitions;
use App\Application\Football\ListPlayers;
use App\Application\Football\ListTeams;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;

final class ListFootballDataTest extends TestCase
{
    public function test_it_lists_competitions(): void
    {
        $competitions = [
            new CompetitionData(2021, 'Premier League', 'PL', null),
        ];
        $provider = $this->providerMock();
        $provider->expects(self::once())
            ->method('competitions')
            ->willReturn($competitions);

        self::assertSame($competitions, (new ListCompetitions($provider))->handle());
    }

    public function test_it_lists_teams_for_a_competition(): void
    {
        $teams = [
            new TeamData(64, 'Liverpool FC', 'Liverpool', null),
        ];
        $provider = $this->providerMock();
        $provider->expects(self::once())
            ->method('teams')
            ->with(2021)
            ->willReturn($teams);

        self::assertSame($teams, (new ListTeams($provider))->handle(2021));
    }

    public function test_it_lists_players_for_a_team(): void
    {
        $players = [
            new PlayerData(117, 'Mohamed Salah', 'Right Winger', null, 'Egypt'),
        ];
        $provider = $this->providerMock();
        $provider->expects(self::once())
            ->method('players')
            ->with(64)
            ->willReturn($players);

        self::assertSame($players, (new ListPlayers($provider))->handle(64));
    }

    private function providerMock(): MockObject&FootballDataProvider
    {
        return $this->createMock(FootballDataProvider::class);
    }
}
