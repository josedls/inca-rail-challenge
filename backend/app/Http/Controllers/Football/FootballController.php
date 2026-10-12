<?php

namespace App\Http\Controllers\Football;

use App\Application\Football\GetSavedCompetition;
use App\Application\Football\GetSavedTeam;
use App\Application\Football\ImportCompetition;
use App\Application\Football\ListSavedCompetitions;
use App\Application\Football\ListSavedPlayers;
use App\Application\Football\ListSavedTeams;
use App\Http\Controllers\Controller;
use App\Http\Resources\Football\CompetitionResource;
use App\Http\Resources\Football\PlayerResource;
use App\Http\Resources\Football\TeamResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class FootballController extends Controller
{
    public function competitions(ListSavedCompetitions $listCompetitions): AnonymousResourceCollection
    {
        return CompetitionResource::collection($listCompetitions->handle());
    }

    public function competition(
        int $competition,
        GetSavedCompetition $getSavedCompetition,
        ImportCompetition $importCompetition,
    ): CompetitionResource {
        $savedCompetition = $getSavedCompetition->handle($competition);

        if ($savedCompetition === null || $getSavedCompetition->needsImport($competition)) {
            $importCompetition->handle($competition);
            $savedCompetition = $getSavedCompetition->handle($competition);
        }

        abort_if($savedCompetition === null, 404, 'Competition not found.');

        return new CompetitionResource($savedCompetition);
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
