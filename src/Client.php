<?php

declare(strict_types=1);

namespace ISklep\Api;

use ISklep\Api\Authorisation\AuthorisationInterface;
use ISklep\Api\Http\HttpClientAdapterInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;

final class Client implements ApiClientInterface
{
    /** @var array<string, string> */
    private array $headers = [];

    public function __construct(
        private readonly HttpClientAdapterInterface $httpClient,
        private readonly AuthorisationInterface     $authorisation,
        private readonly string                     $baseUri = '',
        private readonly ?LoggerInterface           $logger = null,
    ) {}

    public function withHeader(string $name, string $value): self
    {
        $clone = clone $this;
        $clone->headers[$name] = $value;

        return $clone;
    }

    /**
     * @param array<string, mixed> $queryParams
     */
    public function get(string $uri, array $queryParams = []): ResponseInterface
    {
        $url = $this->buildUrl($uri, $queryParams);
        $this->log('info', 'GET request', ['url' => $url]);

        return $this->httpClient->sendRequest($this->createAuthorizedRequest('GET', $url));
    }

    /**
     * @param array<string, mixed> $body
     */
    public function post(string $uri, array $body = []): ResponseInterface
    {
        return $this->sendWithBody('POST', $uri, $body);
    }

    /**
     * @param array<string, mixed> $body
     */
    public function put(string $uri, array $body = []): ResponseInterface
    {
        return $this->sendWithBody('PUT', $uri, $body);
    }

    /**
     * @param array<string, mixed> $body
     */
    public function patch(string $uri, array $body = []): ResponseInterface
    {
        return $this->sendWithBody('PATCH', $uri, $body);
    }

    public function delete(string $uri): ResponseInterface
    {
        $url = $this->baseUri . $uri;
        $this->log('info', 'DELETE request', ['url' => $url]);

        return $this->httpClient->sendRequest($this->createAuthorizedRequest('DELETE', $url));
    }

    /**
     * @param array<string, mixed> $body
     */
    private function sendWithBody(string $method, string $uri, array $body): ResponseInterface
    {
        $url = $this->baseUri . $uri;
        $this->log('info', $method . ' request', ['url' => $url]);
        $request = $this->createAuthorizedRequest($method, $url);
        $stream = $this->httpClient->createStream(json_encode($body, JSON_THROW_ON_ERROR));

        return $this->httpClient->sendRequest($request->withBody($stream));
    }

    /**
     * @param array<string, mixed> $queryParams
     */
    private function buildUrl(string $uri, array $queryParams): string
    {
        $url = $this->baseUri . $uri;

        if ($queryParams !== []) {
            $url .= '?' . http_build_query($queryParams);
        }

        return $url;
    }

    private function createAuthorizedRequest(string $method, string $url): RequestInterface
    {
        $request = $this->httpClient->createRequest($method, $url);

        foreach ($this->headers as $name => $value) {
            $request = $request->withHeader($name, $value);
        }

        return $this->authorisation->authorise($request)
            ->withHeader('Content-Type', 'application/json');
    }

    /**
     * @param array<string, mixed> $context
     */
    private function log(string $level, string $message, array $context = []): void
    {
        $this->logger?->{$level}($message, $context);
    }
}
