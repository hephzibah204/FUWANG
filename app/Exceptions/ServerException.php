<?php

namespace App\Exceptions;

use Throwable;

class ServerException extends AppException
{
    protected $errorType = 'server_error';

    public function __construct(
        string $message = 'Internal server error.',
        string $userMessage = 'An unexpected server error occurred. Our team has been notified.',
        int $code = 500,
        string $severity = 'critical',
        array $contextData = [],
        Throwable $previous = null
    ) {
        parent::__construct($message, $userMessage, $code, $severity, $contextData, $previous);
    }
}
