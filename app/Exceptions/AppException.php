<?php

namespace App\Exceptions;

use Exception;
use Throwable;

abstract class AppException extends Exception
{
    /**
     * @var string
     */
    protected $errorType = 'server_error';

    /**
     * @var string
     */
    protected $userMessage;

    /**
     * @var string
     */
    protected $severity;

    /**
     * @var array
     */
    protected $contextData = [];

    /**
     * AppException constructor.
     *
     * @param string $message Developer message (not shown to user)
     * @param string $userMessage User-friendly message
     * @param int $code HTTP status code
     * @param string $severity Logging severity (info, warning, error, critical)
     * @param array $contextData Additional data for logging
     * @param Throwable|null $previous
     */
    public function __construct(
        string $message = 'An unexpected error occurred.',
        string $userMessage = 'Something went wrong. Please try again later.',
        int $code = 500,
        string $severity = 'error',
        array $contextData = [],
        Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
        $this->userMessage = $userMessage;
        $this->severity = $severity;
        $this->contextData = $contextData;
    }

    public function getErrorType(): string
    {
        return $this->errorType;
    }

    public function getUserMessage(): string
    {
        return $this->userMessage;
    }

    public function getSeverity(): string
    {
        return $this->severity;
    }

    public function getContextData(): array
    {
        return $this->contextData;
    }
}
