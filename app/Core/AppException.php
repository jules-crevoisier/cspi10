<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Exception métier avec message utilisateur en français.
 */
class AppException extends \RuntimeException
{
    public function __construct(
        string $userMessage,
        private readonly string $errorCode = 'APP_ERROR',
        int $httpStatus = 400,
        ?\Throwable $previous = null
    ) {
        parent::__construct($userMessage, $httpStatus, $previous);
    }

    public function getErrorCode(): string
    {
        return $this->errorCode;
    }

    public function getHttpStatus(): int
    {
        return (int) $this->getCode();
    }

    public function getUserMessage(): string
    {
        return $this->getMessage();
    }
}
