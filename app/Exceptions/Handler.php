<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

final class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e): void {
            //
        });
    }

    /**
     * Render an exception into an HTTP response.
     */
    public function render($request, Throwable $e): Response|JsonResponse|\Symfony\Component\HttpFoundation\Response
    {
        if ($request->expectsJson()) {
            return $this->handleApiException($request, $e);
        }

        return parent::render($request, $e);
    }

    /**
     * Handle API exceptions with proper JSON responses.
     */
    private function handleApiException(Request $request, Throwable $e): JsonResponse
    {
        if ($e instanceof ValidationException) {
            return $this->handleValidationException($e);
        }

        if ($e instanceof AuthenticationException) {
            return $this->handleAuthenticationException();
        }

        if ($e instanceof ModelNotFoundException) {
            return $this->handleModelNotFoundException();
        }

        if ($e instanceof NotFoundHttpException) {
            return $this->handleNotFoundHttpException();
        }

        if ($e instanceof HttpException) {
            return $this->handleHttpException($e);
        }

        return $this->handleGenericException($e);
    }

    /**
     * Handle validation exceptions.
     */
    private function handleValidationException(ValidationException $e): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => 'De ingevoerde gegevens zijn ongeldig.',
            'errors' => $e->errors(),
        ], 422);
    }

    /**
     * Handle authentication exceptions.
     */
    private function handleAuthenticationException(): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => 'U moet ingelogd zijn om deze actie uit te voeren.',
        ], 401);
    }

    /**
     * Handle model not found exceptions.
     */
    private function handleModelNotFoundException(): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => 'Het gevraagde item kon niet worden gevonden.',
        ], 404);
    }

    /**
     * Handle not found HTTP exceptions.
     */
    private function handleNotFoundHttpException(): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => 'De gevraagde pagina kon niet worden gevonden.',
        ], 404);
    }

    /**
     * Handle HTTP exceptions.
     */
    private function handleHttpException(HttpException $e): JsonResponse
    {
        $message = $this->getHttpExceptionMessage($e->getStatusCode());

        return response()->json([
            'success' => false,
            'message' => $message,
        ], $e->getStatusCode());
    }

    /**
     * Handle generic exceptions.
     */
    private function handleGenericException(Throwable $e): JsonResponse
    {
        $message = app()->environment('production') 
            ? 'Er is een onverwachte fout opgetreden. Probeer het later opnieuw.'
            : $e->getMessage();

        return response()->json([
            'success' => false,
            'message' => $message,
        ], 500);
    }

    /**
     * Get appropriate message for HTTP status codes.
     */
    private function getHttpExceptionMessage(int $statusCode): string
    {
        return match ($statusCode) {
            400 => 'Ongeldig verzoek.',
            401 => 'U bent niet geautoriseerd om deze actie uit te voeren.',
            403 => 'Deze actie is niet toegestaan.',
            404 => 'De gevraagde resource kon niet worden gevonden.',
            405 => 'Deze methode is niet toegestaan.',
            408 => 'Het verzoek is verlopen.',
            422 => 'De ingevoerde gegevens zijn ongeldig.',
            429 => 'Te veel verzoeken. Probeer het later opnieuw.',
            500 => 'Er is een serverfout opgetreden.',
            502 => 'Bad Gateway.',
            503 => 'De service is tijdelijk niet beschikbaar.',
            504 => 'Gateway Timeout.',
            default => 'Er is een fout opgetreden.',
        };
    }
}
