<?php

declare(strict_types=1);

namespace ISklep\Api\Authorisation;

use Psr\Http\Message\RequestInterface;

interface AuthorisationInterface
{
    public function authorise(RequestInterface $request): RequestInterface;
}
