<?php

namespace App\Support\Exceptions;

use App\Exceptions\AppException;
use App\Exceptions\ValidationException as CustomValidationException;
use Illuminate\Auth\AuthenticationException as LaravelAuthException;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException as LaravelValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

class ErrorHandler
{
    /**
     * Register global exception handling logic.
     */
    public static function register(Exceptions $exceptions): void
    {
        // Custom report logic
        $exceptions->report(function (Throwable $e) {
            self::reportException($e);
            return true; // continue to default Laravel logging
        });

        // Custom render logic
        $exceptions->render(function (Throwable $e, Request $request) {
            if ($e instanceof \App\Exceptions\ServiceNotConfiguredException) {
                return response()->view('errors.service_not_configured', [
                    'message' => $e->getUserMessage(),
                    'reference_id' => $request->attributes->get('correlation_id', 'unknown')
                ], $e->getCode() ?: 503);
            }

            if ($request->is('api/*') || $request->wantsJson()) {
                return self::renderJsonResponse($e, $request);
            }
            return null; // Fallback to Laravel's default HTML response
        });
    }

    /**
     * Log exceptions with proper severity and context.
     */
    protected static function reportException(Throwable $e): void
    {
        // Prevent logging expected validation errors unless debug is true
        if ($e instanceof LaravelValidationException || $e instanceof CustomValidationException) {
            if (!config('app.debug')) {
                return;
            }
        }

        $correlationId = request()->attributes->get('correlation_id', 'unknown');
        
        $logData = [
            'correlation_id' => $correlationId,
            'url' => request()->fullUrl(),
            'method' => request()->method(),
            'ip' => request()->ip(),
            'exception' => get_class($e),
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
        ];

        // Add sanitized request payload
        $payload = request()->except(['password', 'password_confirmation', 'token', 'api_token', 'secret', 'credit_card', 'cvv']);
        if (!empty($payload)) {
            $logData['request_payload'] = $payload;
        }

        if ($e instanceof AppException) {
            $severity = $e->getSeverity();
            $logData['error_type'] = $e->getErrorType();
            $logData['user_message'] = $e->getUserMessage();
            $logData = array_merge($logData, $e->getContextData());
        } else {
            $severity = static::determineSeverity($e);
        }

        // Avoid logging sensitive information
        $safeLogData = static::sanitizeLogData($logData);

        Log::log($severity, $e->getMessage(), $safeLogData);
    }

    /**
     * Determine logging severity based on exception type.
     */
    protected static function determineSeverity(Throwable $e): string
    {
        if ($e instanceof HttpExceptionInterface) {
            $code = $e->getStatusCode();
            if ($code >= 500) return 'error';
            if ($code === 404 || $code === 403 || $code === 401) return 'warning';
            return 'info';
        }

        return 'error';
    }

    /**
     * Render standardized JSON response for APIs.
     */
    protected static function renderJsonResponse(Throwable $e, Request $request)
    {
        $correlationId = $request->attributes->get('correlation_id');

        $response = [
            'status' => false,
            'error' => [
                'type' => 'server_error',
                'message' => __('errors.server_error'),
                'reference_id' => $correlationId,
            ]
        ];

        $statusCode = 500;

        // Handle JSON Parsing errors (malformed JSON)
        if ($e instanceof \Symfony\Component\HttpKernel\Exception\BadRequestHttpException && str_contains($e->getMessage(), 'JSON')) {
            $statusCode = 400;
            $response['error']['type'] = 'invalid_json';
            $response['error']['message'] = __('errors.invalid_json');
        }
        // Handle custom application exceptions
        elseif ($e instanceof AppException) {
            $statusCode = $e->getCode() ?: 500;
            $response['error']['type'] = $e->getErrorType();
            $response['error']['message'] = $e->getUserMessage();
            
            if ($e instanceof CustomValidationException) {
                $response['error']['details'] = $e->getValidationErrors();
            }
            
            if (config('app.debug')) {
                $response['error']['debug_message'] = $e->getMessage();
            }
        } 
        // Handle Laravel validation exceptions
        elseif ($e instanceof LaravelValidationException) {
            $statusCode = 422;
            $response['error']['type'] = 'validation_error';
            $response['error']['message'] = __('errors.validation');
            $response['error']['details'] = $e->errors();
        } 
        // Handle Laravel authentication exceptions
        elseif ($e instanceof LaravelAuthException) {
            $statusCode = 401;
            $response['error']['type'] = 'authentication_error';
            $response['error']['message'] = __('errors.authentication');
        }
        // Handle HTTP exceptions (like 404 Not Found)
        elseif ($e instanceof HttpExceptionInterface) {
            $statusCode = $e->getStatusCode();
            $response['error']['type'] = static::getTypeForHttpCode($statusCode);
            $response['error']['message'] = static::getMessageForHttpCode($statusCode);
        }

        // Add trace and raw message in debug mode for unexpected errors
        if (config('app.debug') && !($e instanceof AppException)) {
            $response['error']['debug_message'] = $e->getMessage();
            $response['error']['trace'] = collect($e->getTrace())->take(5)->toArray();
        }

        return response()->json($response, $statusCode);
    }

    protected static function getTypeForHttpCode(int $code): string
    {
        return match($code) {
            400 => 'bad_request',
            401 => 'unauthorized',
            403 => 'forbidden',
            404 => 'not_found',
            405 => 'method_not_allowed',
            422 => 'unprocessable_entity',
            429 => 'too_many_requests',
            503 => 'service_unavailable',
            default => 'server_error'
        };
    }

    protected static function getMessageForHttpCode(int $code): string
    {
        return match($code) {
            400 => __('errors.bad_request'),
            401 => __('errors.authentication'),
            403 => __('errors.authorization'),
            404 => __('errors.not_found'),
            405 => __('errors.method_not_allowed'),
            429 => __('errors.too_many_requests'),
            503 => __('errors.service_unavailable'),
            default => __('errors.server_error')
        };
    }

    /**
     * Filter out sensitive keys from context data before logging.
     */
    protected static function sanitizeLogData(array $data): array
    {
        $sensitiveKeys = ['password', 'password_confirmation', 'token', 'api_token', 'secret', 'credit_card', 'cvv'];
        
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $data[$key] = static::sanitizeLogData($value);
            } elseif (is_string($key)) {
                foreach ($sensitiveKeys as $sensitiveKey) {
                    if (str_contains(strtolower($key), $sensitiveKey)) {
                        $data[$key] = '[REDACTED]';
                        break;
                    }
                }
            }
        }
        
        return $data;
    }
}
