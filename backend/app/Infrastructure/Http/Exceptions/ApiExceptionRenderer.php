<?php

namespace App\Infrastructure\Http\Exceptions;

use App\Domain\Football\FootballDataApiException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Exceptions\UnauthorizedException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

final class ApiExceptionRenderer
{
    private const FIXED_ERROR_RESPONSES = [
        UnauthorizedException::class => [Response::HTTP_FORBIDDEN, 'Forbidden.'],
        ModelNotFoundException::class => [Response::HTTP_NOT_FOUND, 'Resource not found.'],
        NotFoundHttpException::class => [Response::HTTP_NOT_FOUND, 'Resource not found.'],
    ];

    public function __invoke(Throwable $exception, Request $request): ?JsonResponse
    {
        if (! $this->shouldRenderJson($request)) {
            return null;
        }

        if ($exception instanceof ValidationException) {
            return $this->validationError($exception);
        }

        if ($exception instanceof AuthenticationException) {
            return $this->error(
                $exception->getMessage() ?: 'Unauthenticated.',
                Response::HTTP_UNAUTHORIZED,
            );
        }

        if ($exception instanceof AuthorizationException) {
            return $this->error(
                $exception->getMessage() ?: 'Forbidden.',
                Response::HTTP_FORBIDDEN,
            );
        }

        if ($exception instanceof FootballDataApiException) {
            return $this->error($exception->getMessage(), $exception->statusCode);
        }

        $fixedError = $this->fixedErrorResponse($exception);

        if ($fixedError !== null) {
            return $fixedError;
        }

        if ($exception instanceof HttpExceptionInterface) {
            return $this->error(
                $exception->getMessage() ?: 'The request could not be completed.',
                $exception->getStatusCode(),
                $exception->getHeaders(),
            );
        }

        return $this->error('Internal server error.', Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    public function shouldRenderJson(Request $request): bool
    {
        $route = $request->route();

        return $request->expectsJson()
            || ($route instanceof Route && in_array('api', $route->gatherMiddleware(), true));
    }

    private function validationError(ValidationException $exception): JsonResponse
    {
        return response()->json([
            'message' => 'The given data was invalid.',
            'errors' => $exception->errors(),
        ], Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    private function fixedErrorResponse(Throwable $exception): ?JsonResponse
    {
        foreach (self::FIXED_ERROR_RESPONSES as $exceptionClass => [$status, $message]) {
            if ($exception instanceof $exceptionClass) {
                return $this->error($message, $status);
            }
        }

        return null;
    }

    private function error(string $message, int $status, array $headers = []): JsonResponse
    {
        return response()->json([
            'message' => $message,
        ], $status, $headers);
    }
}
