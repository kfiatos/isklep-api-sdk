<?php

declare(strict_types=1);

namespace ISklep\Api;

use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ResponseFactoryInterface;

interface HttpClientFactoryInterface
{
    public function create(
        RequestFactoryInterface $requestFactory,
        ResponseFactoryInterface $responseFactory,
    ): ClientInterface;
}
