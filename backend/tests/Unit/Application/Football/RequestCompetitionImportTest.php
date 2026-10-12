<?php

namespace Tests\Unit\Application\Football;

use App\Application\Football\Contracts\CompetitionImportDispatcher;
use App\Application\Football\Contracts\FootballCatalogReader;
use App\Application\Football\Data\CompetitionData;
use App\Application\Football\GetSavedCompetition;
use App\Application\Football\RequestCompetitionImport;
use App\Domain\Football\CompetitionImportStatus;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;

final class RequestCompetitionImportTest extends TestCase
{
    public function test_it_queues_a_new_import(): void
    {
        $dispatcher = $this->dispatcherMock();
        $dispatcher->expects(self::once())
            ->method('dispatch')
            ->with(2021);

        $reader = $this->readerMock();
        $reader->expects(self::once())
            ->method('competition')
            ->with(2021)
            ->willReturn(null);

        $result = $this->service($reader, $dispatcher)->handle(2021);

        self::assertSame(2021, $result->competitionId);
        self::assertSame('queued', $result->status);
        self::assertSame(202, $result->httpStatus);
    }

    public function test_it_does_not_queue_an_import_while_processing(): void
    {
        $dispatcher = $this->dispatcherMock();
        $dispatcher->expects(self::never())->method('dispatch');

        $reader = $this->readerMock();
        $reader->method('competition')->willReturn(
            new CompetitionData(2021, 'Premier League', 'PL', null, CompetitionImportStatus::Processing),
        );

        $result = $this->service($reader, $dispatcher)->handle(2021);

        self::assertSame('processing', $result->status);
        self::assertSame(202, $result->httpStatus);
    }

    public function test_it_does_not_queue_an_import_when_completed(): void
    {
        $dispatcher = $this->dispatcherMock();
        $dispatcher->expects(self::never())->method('dispatch');

        $reader = $this->readerMock();
        $reader->method('competition')->willReturn(
            new CompetitionData(2021, 'Premier League', 'PL', null, CompetitionImportStatus::Completed),
        );

        $result = $this->service($reader, $dispatcher)->handle(2021);

        self::assertSame('completed', $result->status);
        self::assertSame(200, $result->httpStatus);
    }

    private function service(
        MockObject&FootballCatalogReader $reader,
        MockObject&CompetitionImportDispatcher $dispatcher,
    ): RequestCompetitionImport {
        return new RequestCompetitionImport(
            new GetSavedCompetition($reader),
            $dispatcher,
        );
    }

    private function readerMock(): MockObject&FootballCatalogReader
    {
        return $this->createMock(FootballCatalogReader::class);
    }

    private function dispatcherMock(): MockObject&CompetitionImportDispatcher
    {
        return $this->createMock(CompetitionImportDispatcher::class);
    }
}
