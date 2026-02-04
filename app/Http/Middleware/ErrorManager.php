<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Throwable;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Http\Exceptions\ThrottleRequestsException;



/**
 * Global error handler middleware for consistent API responses.
 * 
 * Note: For a production API, this provides:
 * - Consistent error format across all endpoints
 * - Proper logging and monitoring
 * - Debug mode for development
 * 
 * For this test's scope, this is optional but demonstrates
 * production-ready error handling patterns.
 */
class ErrorManager
{
    /**
     * Handle an incoming request and catch any exceptions.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            $response = $next($request);
            
            // Handle HTTP error status codes
            if ($response->getStatusCode() >= 400) {
                return $this->handleHttpError($request, $response);
            }
            
            return $response;
        } catch (Throwable $exception) {
            return $this->handleException($request, $exception);
        }
    }

    /**
     * Handle exceptions and return appropriate JSON responses.
     */
    private function handleException(Request $request, Throwable $exception): JsonResponse
    {
        // Log the exception for debugging
        logger()->error('ErrorManager caught exception: ' . $exception->getMessage(), [
            'exception' => $exception,
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'ip' => $request->ip(),
        ]);

        return match (true) {
            $exception instanceof ValidationException => $this->handleValidationException($exception),
            $exception instanceof ModelNotFoundException => $this->handleModelNotFoundException($exception),
            $exception instanceof NotFoundHttpException => $this->handleNotFoundException(),
            $exception instanceof ThrottleRequestsException => $this->handleThrottleException($exception),
            $exception instanceof HttpException => $this->handleHttpException($exception),
            default => $this->handleGenericException($exception),
        };
    }

    /**
     * Handle HTTP error responses.
     */
    private function handleHttpError(Request $request, Response $response): JsonResponse
    {
        $statusCode = $response->getStatusCode();
        
        $errorMessages = [
            400 => ['error' => 'Bad Request', 'message' => 'The request could not be understood by the server.'],
            404 => ['error' => 'Not Found', 'message' => 'The requested resource could not be found.'],
            405 => ['error' => 'Method Not Allowed', 'message' => 'The HTTP method is not allowed for this resource.'],
            422 => ['error' => 'Unprocessable Entity', 'message' => 'The request was well-formed but contains semantic errors.'],
            429 => ['error' => 'Too Many Requests', 'message' => 'Too many requests have been made in a given time period.'],
            500 => ['error' => 'Internal Server Error', 'message' => 'An unexpected error occurred on the server.'],
            502 => ['error' => 'Bad Gateway', 'message' => 'The server received an invalid response from an upstream server.'],
            503 => ['error' => 'Service Unavailable', 'message' => 'The server is currently unavailable.'],
        ];

        $errorData = $errorMessages[$statusCode] ?? [
            'error' => 'HTTP Error',
            'message' => 'An HTTP error occurred.'
        ];

        return response()->json([
            'success' => false,
            'error' => $errorData['error'],
            'message' => $errorData['message'],
            'status' => $statusCode,
            'timestamp' => now()->toISOString(),
        ], $statusCode);
    }

    /**
     * Handle validation exceptions.
     */
    private function handleValidationException(ValidationException $exception): JsonResponse
    {
        return response()->json([
            'success' => false,
            'error' => 'Validation Failed',
            'message' => 'The given data was invalid.',
            'errors' => $exception->errors(),
            'status' => 422,
            'timestamp' => now()->toISOString(),
        ], 422);
    }

    /**
     * Handle model not found exceptions.
     */
    private function handleModelNotFoundException(ModelNotFoundException $exception): JsonResponse
    {
        $model = class_basename($exception->getModel());
        
        return response()->json([
            'success' => false,
            'error' => 'Resource Not Found',
            'message' => "The requested {$model} could not be found.",
            'status' => 404,
            'timestamp' => now()->toISOString(),
        ], 404);
    }

    /**
     * Handle not found exceptions.
     */
    private function handleNotFoundException(): JsonResponse
    {
        return response()->json([
            'success' => false,
            'error' => 'Not Found',
            'message' => 'The requested resource could not be found.',
            'status' => 404,
            'timestamp' => now()->toISOString(),
        ], 404);
    }


    /**
     * Handle throttle exceptions.
     */
    private function handleThrottleException(ThrottleRequestsException $exception): JsonResponse
    {
        return response()->json([
            'success' => false,
            'error' => 'Too Many Requests',
            'message' => 'Too many requests have been made. Please try again later.',
            'retry_after' => $exception->getHeaders()['Retry-After'] ?? null,
            'status' => 429,
            'timestamp' => now()->toISOString(),
        ], 429);
    }

    /**
     * Handle HTTP exceptions.
     */
    private function handleHttpException(HttpException $exception): JsonResponse
    {
        return response()->json([
            'success' => false,
            'error' => 'HTTP Error',
            'message' => $exception->getMessage() ?: 'An HTTP error occurred.',
            'status' => $exception->getStatusCode(),
            'timestamp' => now()->toISOString(),
        ], $exception->getStatusCode());
    }

    /**
     * Handle generic exceptions.
     */
    private function handleGenericException(Throwable $exception): JsonResponse
    {
        $isDevelopment = config('app.debug', false);
        
        $response = [
            'success' => false,
            'error' => 'Internal Server Error',
            'message' => $isDevelopment 
                ? $exception->getMessage() 
                : 'An unexpected error occurred. Please try again later.',
            'status' => 500,
            'timestamp' => now()->toISOString(),
        ];

        // Add debug information only in development
        if ($isDevelopment) {
            $response['debug'] = [
                'exception' => get_class($exception),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $exception->getTraceAsString(),
            ];
        }

        return response()->json($response, 500);
    }
}
