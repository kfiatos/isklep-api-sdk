<?php

declare(strict_types=1);

namespace ISklep\Api\Exceptions;

class UnauthorizedException extends ApiException
{
    public function __construct(
        string $responseBody = '',
        ?\Throwable $previous = null,
    ) {
        parent::__construct('Unauthorized', 401, $responseBody, $previous);
    }
}
