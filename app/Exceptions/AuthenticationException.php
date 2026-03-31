<?php

namespace App\Exceptions;

use Throwable;

class AuthenticationException extends AppException
{
    protected $errorType = 'authentication_error';

    public function __construct(
        string $message = 'Authentication failed.',
        string $userMessage = 'You must be logged in to perform this action.',
        int $code = 401,
        string $severity = 'info',
        array $contextData = [],
        Throwable $previous = null
    ) {
        parent::__construct($message, $userMessage, $code, $severity, $contextData, $previous);
    }
}
