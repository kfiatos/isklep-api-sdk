<?php

declare(strict_types=1);

namespace ISklep\Api;

use ISklep\Api\Exceptions\ApiException;
use Psr\Http\Message\ResponseInterface;

interface ResponseDecoderInterface
{
    /**
     * @return array<string, mixed>
     *
     * @throws ApiException
     */
    public function decode(ResponseInterface $response): array;
}
