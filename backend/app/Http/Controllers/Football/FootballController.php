<?php

namespace App\Http\Controllers\Football;

use App\Application\Football\GetSavedCompetition;
use App\Application\Football\GetSavedTeam;
use App\Application\Football\ListSavedCompetitions;
use App\Application\Football\ListSavedPlayers;
use App\Application\Football\ListSavedTeams;
use App\Http\Controllers\Controller;
use App\Http\Resources\Football\CompetitionResource;
use App\Http\Resources\Football\PlayerResource;
use App\Http\Resources\Football\TeamResource;
use App\Jobs\Football\ImportCompetitionJob;
use App\Jobs\Football\SyncCompetitionsJob;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class FootballController extends Controller
{
    public function competitions(ListSavedCompetitions $listCompetitions): AnonymousResourceCollection|JsonResponse
    {
        $competitions = $listCompetitions->handle();

        if ($competitions === []) {
            SyncCompetitionsJob::dispatch();

            return response()->json([
                'data' => [
                    'status' => 'queued',
                ],
            ], 202);
        }

        return CompetitionResource::collection($competitions);
    }

    public function competition(
        int $competition,
        GetSavedCompetition $getSavedCompetition,
    ): CompetitionResource {
        $savedCompetition = $getSavedCompetition->handle($competition);

        abort_if($savedCompetition === null, 404, 'Competition not found.');

        return new CompetitionResource($savedCompetition);
    }

    public function importCompetition(
        int $competition,
        GetSavedCompetition $getSavedCompetition,
    ): JsonResponse {
        $savedCompetition = $getSavedCompetition->handle($competition);
        $status = $savedCompetition?->importStatus;

        if ($status === 'processing') {
            return $this->importStatusResponse($competition, 'processing');
        }

        if ($status === 'completed') {
            return $this->importStatusResponse($competition, 'completed', 200);
        }

        ImportCompetitionJob::dispatch($competition);

        return $this->importStatusResponse($competition, 'queued');
    }

    private function importStatusResponse(int $competition, string $status, int $responseStatus = 202): JsonResponse
    {
        return response()->json([
            'data' => [
                'competition_id' => $competition,
                'status' => $status,
            ],
        ], $responseStatus);
    }

    public function teams(ListSavedTeams $listSavedTeams): AnonymousResourceCollection
    {
        return TeamResource::collection($listSavedTeams->handle());
    }

    public function team(int $team, GetSavedTeam $getSavedTeam): TeamResource
    {
        $savedTeam = $getSavedTeam->handle($team);

        abort_if($savedTeam === null, 404, 'Team not found.');

        return new TeamResource($savedTeam);
    }

    public function players(ListSavedPlayers $listSavedPlayers): AnonymousResourceCollection
    {
        return PlayerResource::collection($listSavedPlayers->handle());
    }
}
