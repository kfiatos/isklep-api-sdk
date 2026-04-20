<?php

declare(strict_types=1);

namespace ISklep\Api;

use ISklep\Api\Exceptions\ClientException;
use ISklep\Api\Exceptions\DeserializationException;
use ISklep\Api\Exceptions\HttpException;
use ISklep\Api\Exceptions\ServerException;
use ISklep\Api\Exceptions\UnauthorizedException;
use Psr\Http\Message\ResponseInterface;

final class WrappedResponseDecoder implements ResponseDecoderInterface
{
    /**
     * @return array<string, mixed>
     */
    public function decode(ResponseInterface $response): array
    {
        $status = $response->getStatusCode();
        $body = (string) $response->getBody();

        if ($status === 401) {
            throw new UnauthorizedException($body);
        }

        if ($status >= 500) {
            throw new ServerException('Server error', $status, $body);
        }

        if ($body === '') {
            return [];
        }

        try {
            $data = json_decode($body, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            if ($status >= 400) {
                throw new ClientException('Client error', $status, $body);
            }
            throw new DeserializationException('Invalid JSON: ' . $e->getMessage(), $status, $body, $e);
        }

        if (!is_array($data)) {
            throw new DeserializationException('Expected JSON object', $status, $body);
        }

        if (!isset($data['success']) || $data['success'] !== true) {
            $error = $data['error'] ?? [];
            $messages = isset($error['messages']) && is_array($error['messages']) ? $error['messages'] : null;
            $message = $messages !== null && $messages !== []
                ? implode(', ', $messages)
                : (string) ($error['message'] ?? $error['reason'] ?? 'API error');

            throw new HttpException($message, $status, $body);
        }

        /** @var array<string, mixed> */
        return $data['data'] ?? [];
    }
}
