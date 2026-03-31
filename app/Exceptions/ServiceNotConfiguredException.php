<?php

namespace App\Exceptions;

use Throwable;

class ServiceNotConfiguredException extends AppException
{
    protected $errorType = 'service_not_configured';

    public function __construct(
        string $message = 'Service not configured.',
        string $userMessage = 'The requested service is currently unavailable or not configured. Please contact support.',
        int $code = 503,
        string $severity = 'warning',
        array $contextData = [],
        Throwable $previous = null
    ) {
        parent::__construct($message, $userMessage, $code, $severity, $contextData, $previous);
    }
}
