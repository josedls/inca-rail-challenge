<?php

namespace Tests\Unit\Jobs\Football;

use App\Application\Football\Contracts\FootballCatalogWriter;
use App\Application\Football\Contracts\FootballDataProvider;
use App\Application\Football\Data\CompetitionData;
use App\Application\Football\Data\PlayerData;
use App\Application\Football\Data\TeamData;
use App\Jobs\Football\ImportCompetitionJob;
use App\Jobs\Football\ImportCompetitionTeamsJob;
use App\Jobs\Football\ImportTeamPlayersJob;
use App\Jobs\Football\SyncCompetitionsJob;
use DateTimeInterface;
use Illuminate\Queue\Middleware\RateLimited;
use Illuminate\Support\Facades\Queue;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;

final class ImportFootballJobsTest extends TestCase
{
    public function test_competition_job_saves_data_and_dispatches_team_import(): void
    {
        Queue::fake();
        $provider = $this->providerMock();
        $writer = $this->writerMock();
        $competition = new CompetitionData(2021, 'Premier League', 'PL', null);

        $provider->expects(self::once())
            ->method('competition')
            ->with(2021)
            ->willReturn($competition);
        $writer->expects(self::once())
            ->method('saveCompetitions')
            ->with([$competition]);

        (new ImportCompetitionJob(2021))->handle($provider, $writer);

        Queue::assertPushed(ImportCompetitionTeamsJob::class);
        self::assertInstanceOf(RateLimited::class, (new ImportCompetitionJob(2021))->middleware()[0]);
        self::assertSame(0, (new ImportCompetitionJob(2021))->tries);
        self::assertInstanceOf(DateTimeInterface::class, (new ImportCompetitionJob(2021))->retryUntil());
    }

    public function test_team_job_saves_data_and_dispatches_player_imports(): void
    {
        Queue::fake();
        $provider = $this->providerMock();
        $writer = $this->writerMock();
        $teams = [new TeamData(64, 'Liverpool FC', 'Liverpool', null)];

        $provider->expects(self::once())
            ->method('teams')
            ->with(2021)
            ->willReturn($teams);
        $writer->expects(self::once())
            ->method('saveTeams')
            ->with(2021, $teams);

        (new ImportCompetitionTeamsJob(2021))->handle($provider, $writer);

        Queue::assertPushed(ImportTeamPlayersJob::class);
    }

    public function test_player_job_saves_the_team_squad(): void
    {
        $provider = $this->providerMock();
        $writer = $this->writerMock();
        $players = [new PlayerData(117, 'Mohamed Salah', 'Right Winger', 11, null, 'Egypt')];

        $provider->expects(self::once())
            ->method('players')
            ->with(64)
            ->willReturn($players);
        $writer->expects(self::once())
            ->method('savePlayers')
            ->with(64, $players);

        (new ImportTeamPlayersJob(2021, 64))->handle($provider, $writer);

        self::assertInstanceOf(RateLimited::class, (new ImportTeamPlayersJob(2021, 64))->middleware()[0]);
    }

    public function test_competition_sync_saves_the_available_competitions(): void
    {
        $provider = $this->providerMock();
        $writer = $this->writerMock();
        $competitions = [new CompetitionData(2021, 'Premier League', 'PL', null)];

        $provider->expects(self::once())
            ->method('competitions')
            ->willReturn($competitions);
        $writer->expects(self::once())
            ->method('saveCompetitions')
            ->with($competitions);

        (new SyncCompetitionsJob)->handle($provider, $writer);

        self::assertSame('football-competitions', (new SyncCompetitionsJob)->uniqueId());
        self::assertInstanceOf(RateLimited::class, (new SyncCompetitionsJob)->middleware()[0]);
    }

    private function providerMock(): MockObject&FootballDataProvider
    {
        return $this->createMock(FootballDataProvider::class);
    }

    private function writerMock(): MockObject&FootballCatalogWriter
    {
        return $this->createMock(FootballCatalogWriter::class);
    }
}
