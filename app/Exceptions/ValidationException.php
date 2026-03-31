<?php

namespace App\Exceptions;

use Throwable;

class ValidationException extends AppException
{
    protected $errorType = 'validation_error';
    protected $validationErrors;

    public function __construct(
        string $message = 'Validation failed.',
        string $userMessage = 'Please check your input and try again.',
        array $validationErrors = [],
        string $severity = 'info',
        Throwable $previous = null
    ) {
        parent::__construct($message, $userMessage, 422, $severity, [], $previous);
        $this->validationErrors = $validationErrors;
    }

    public function getValidationErrors(): array
    {
        return $this->validationErrors;
    }
}
