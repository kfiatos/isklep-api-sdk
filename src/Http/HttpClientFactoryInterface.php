<?php

declare(strict_types=1);

namespace ISklep\Api\Http;

use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;

interface HttpClientFactoryInterface extends
    ClientInterface,
    RequestFactoryInterface,
    StreamFactoryInterface {}
