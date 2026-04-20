<?php

declare(strict_types=1);

namespace ISklep\Api\Authorisation;

use Psr\Http\Message\RequestInterface;

final class BasicAuthorisation implements AuthorisationInterface
{
    public function __construct(
        private readonly string $username,
        private readonly string $password,
    ) {}

    public function authorise(RequestInterface $request): RequestInterface
    {
        $credentials = base64_encode("{$this->username}:{$this->password}");

        return $request->withHeader('Authorization', "Basic {$credentials}");
    }
}
