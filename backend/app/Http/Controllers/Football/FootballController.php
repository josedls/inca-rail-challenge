<?php

namespace App\Http\Controllers\Football;

use App\Application\Football\ListCompetitions;
use App\Application\Football\ListPlayers;
use App\Application\Football\ListTeams;
use App\Http\Controllers\Controller;
use App\Http\Resources\Football\CompetitionResource;
use App\Http\Resources\Football\PlayerResource;
use App\Http\Resources\Football\TeamResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class FootballController extends Controller
{
    public function competitions(ListCompetitions $listCompetitions): AnonymousResourceCollection
    {
        return CompetitionResource::collection($listCompetitions->handle());
    }

    public function teams(int $competition, ListTeams $listTeams): AnonymousResourceCollection
    {
        return TeamResource::collection($listTeams->handle($competition));
    }

    public function players(int $team, ListPlayers $listPlayers): AnonymousResourceCollection
    {
        return PlayerResource::collection($listPlayers->handle($team));
    }
}
