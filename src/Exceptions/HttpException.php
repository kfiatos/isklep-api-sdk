<?php

declare(strict_types=1);

namespace ISklep\Api\Exceptions;

class HttpException extends ApiException
{
    private ?string $reasonCode = null;
    /** @var string[] */
    private array $messages = [];

    public function __construct(
        string $message,
        private readonly int $statusCode,
        private readonly string $responseBody,
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, $statusCode, $responseBody, $previous);
        $this->parseResponseBody();
    }

    private function parseResponseBody(): void
    {
        try {
            $data = json_decode($this->responseBody, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            return;
        }

        if (is_array($data) && isset($data['error'])) {
            $this->reasonCode = $data['error']['reason_code'] ?? null;
            $this->messages = $data['error']['messages'] ?? [];
        }
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getResponseBody(): string
    {
        return $this->responseBody;
    }

    public function getReasonCode(): ?string
    {
        return $this->reasonCode;
    }

    /**
     * @return string[]
     */
    public function getMessages(): array
    {
        return $this->messages;
    }
}
