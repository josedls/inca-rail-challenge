<?php

namespace App\Http\Controllers\Football;

use App\Application\Football\GetSavedCompetition;
use App\Application\Football\GetSavedTeam;
use App\Application\Football\ListSavedCompetitions;
use App\Application\Football\ListSavedPlayers;
use App\Application\Football\ListSavedTeams;
use App\Application\Football\RequestCompetitionImport;
use App\Application\Football\RequestCompetitionSync;
use App\Http\Controllers\Controller;
use App\Http\Resources\Football\CompetitionResource;
use App\Http\Resources\Football\PlayerResource;
use App\Http\Resources\Football\TeamResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class FootballController extends Controller
{
    public function __construct(
        private readonly RequestCompetitionImport $requestCompetitionImport,
        private readonly RequestCompetitionSync $requestCompetitionSync,
    ) {}

    public function competitions(ListSavedCompetitions $listCompetitions): AnonymousResourceCollection|JsonResponse
    {
        return CompetitionResource::collection($listCompetitions->handle());
    }

    public function syncCompetitions(): JsonResponse
    {
        $this->requestCompetitionSync->handle();

        return response()->json([
            'data' => [
                'status' => 'queued',
            ],
        ], 202);
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
    ): JsonResponse {
        $result = $this->requestCompetitionImport->handle($competition);

        return response()->json([
            'data' => $result->toArray(),
        ], $result->httpStatus);
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
