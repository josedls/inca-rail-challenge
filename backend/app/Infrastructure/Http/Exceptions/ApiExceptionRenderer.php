<?php

namespace App\Infrastructure\Http\Exceptions;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Exceptions\UnauthorizedException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

final class ApiExceptionRenderer
{
    private const FIXED_ERROR_RESPONSES = [
        UnauthorizedException::class => [403, 'Forbidden.'],
        ModelNotFoundException::class => [404, 'Resource not found.'],
        NotFoundHttpException::class => [404, 'Resource not found.'],
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
                401,
            );
        }

        if ($exception instanceof AuthorizationException) {
            return $this->error(
                $exception->getMessage() ?: 'Forbidden.',
                403,
            );
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

        return $this->error('Internal server error.', 500);
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
        ], 422);
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
