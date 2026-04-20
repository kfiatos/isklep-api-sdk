<?php

declare(strict_types=1);

namespace ISklep\Api\Exceptions;

class ClientException extends ApiException
{
    public function __construct(
        string $message,
        private readonly int $statusCode = 0,
        private readonly string $responseBody = '',
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, $statusCode, $responseBody, $previous);
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getResponseBody(): string
    {
        return $this->responseBody;
    }
}
