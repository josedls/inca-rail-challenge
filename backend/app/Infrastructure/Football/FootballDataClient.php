<?php

namespace App\Infrastructure\Football;

use App\Application\Football\Contracts\FootballDataProvider;
use App\Application\Football\Data\CompetitionData;
use App\Application\Football\Data\PlayerData;
use App\Application\Football\Data\TeamData;
use App\Domain\Football\FootballDataApiException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Symfony\Component\HttpFoundation\Response;

final class FootballDataClient implements FootballDataProvider
{
    private const SERVICE_UNAVAILABLE_MESSAGE = 'Football data service is unavailable.';

    private const INVALID_RESPONSE_MESSAGE = 'Football data service returned an invalid response.';

    public function __construct(
        private readonly PendingRequest $client,
    ) {}

    public function competitions(): array
    {
        $items = $this->itemsFrom(
            $this->get('/competitions'),
            'competitions'
        );

        return $this->mapCompetitions($items);
    }

    public function teams(int $competitionId): array
    {
        $items = $this->itemsFrom(
            $this->get("/competitions/{$competitionId}/teams"),
            'teams',
        );

        return $this->mapTeams($items);
    }

    public function players(int $teamId): array
    {
        $items = $this->itemsFrom(
            $this->get("/teams/{$teamId}"),
            'squad'
        );

        return $this->mapPlayers($items);
    }

    private function get(string $uri): array
    {
        try {
            $response = $this->client->get($uri);
        } catch (ConnectionException $exception) {
            throw new FootballDataApiException(
                self::SERVICE_UNAVAILABLE_MESSAGE,
                Response::HTTP_SERVICE_UNAVAILABLE,
                $exception,
            );
        }

        if ($response->status() === Response::HTTP_TOO_MANY_REQUESTS) {
            throw new FootballDataApiException(
                'Football data rate limit exceeded.',
                Response::HTTP_TOO_MANY_REQUESTS,
            );
        }

        if ($response->serverError()) {
            throw new FootballDataApiException(
                self::SERVICE_UNAVAILABLE_MESSAGE,
                Response::HTTP_SERVICE_UNAVAILABLE,
            );
        }

        if ($response->failed()) {
            throw $this->invalidResponse();
        }

        return $this->payloadFrom($response->json());
    }

    private function itemsFrom(array $payload, string $key): array
    {
        $items = $payload[$key] ?? null;

        if (! is_array($items)) {
            throw $this->invalidResponse();
        }

        return $items;
    }

    private function mapCompetitions(array $items): array
    {
        $competitions = [];

        foreach ($items as $item) {
            $competitions[] = $this->mapCompetition($item);
        }

        return $competitions;
    }

    private function mapTeams(array $items): array
    {
        $teams = [];

        foreach ($items as $item) {
            $teams[] = $this->mapTeam($item);
        }

        return $teams;
    }

    private function mapPlayers(array $items): array
    {
        $players = [];

        foreach ($items as $item) {
            $players[] = $this->mapPlayer($item);
        }

        return $players;
    }

    private function mapCompetition(array $competition): CompetitionData
    {
        if (! isset($competition['id'], $competition['name'])) {
            throw $this->invalidResource('competition');
        }

        return new CompetitionData(
            (int) $competition['id'],
            (string) $competition['name'],
            isset($competition['code']) ? (string) $competition['code'] : null,
            isset($competition['emblem']) ? (string) $competition['emblem'] : null,
        );
    }

    private function mapTeam(array $team): TeamData
    {
        if (! isset($team['id'], $team['name'])) {
            throw $this->invalidResource('team');
        }

        return new TeamData(
            (int) $team['id'],
            (string) $team['name'],
            isset($team['shortName']) ? (string) $team['shortName'] : null,
            isset($team['crest']) ? (string) $team['crest'] : null,
        );
    }

    private function mapPlayer(array $player): PlayerData
    {
        if (! isset($player['id'], $player['name'])) {
            throw $this->invalidResource('player');
        }

        return new PlayerData(
            (int) $player['id'],
            (string) $player['name'],
            isset($player['position']) ? (string) $player['position'] : null,
            isset($player['dateOfBirth']) ? (string) $player['dateOfBirth'] : null,
            isset($player['nationality']) ? (string) $player['nationality'] : null,
        );
    }

    private function payloadFrom(mixed $payload): array
    {
        if (! is_array($payload)) {
            throw $this->invalidResponse();
        }

        return $payload;
    }

    private function invalidResponse(): FootballDataApiException
    {
        return new FootballDataApiException(
            self::INVALID_RESPONSE_MESSAGE,
            Response::HTTP_BAD_GATEWAY,
        );
    }

    private function invalidResource(string $resource): FootballDataApiException
    {
        return new FootballDataApiException(
            "Football data service returned an invalid {$resource}.",
            Response::HTTP_BAD_GATEWAY,
        );
    }
}
