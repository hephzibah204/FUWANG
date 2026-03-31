<?php

namespace App\Exceptions;

use Throwable;

class NetworkException extends AppException
{
    protected $errorType = 'network_error';

    public function __construct(
        string $message = 'Network request failed.',
        string $userMessage = 'There was a problem communicating with an external service. Please try again later.',
        int $code = 502,
        string $severity = 'error',
        array $contextData = [],
        Throwable $previous = null
    ) {
        parent::__construct($message, $userMessage, $code, $severity, $contextData, $previous);
    }
}
