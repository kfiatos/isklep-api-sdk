<?php

declare(strict_types=1);

namespace ISklep\Api\Tests\Response;

use GuzzleHttp\Psr7\Response;
use ISklep\Api\Exceptions\HttpException;
use ISklep\Api\Exceptions\ServerException;
use ISklep\Api\Exceptions\UnauthorizedException;
use ISklep\Api\WrappedResponseDecoder;
use PHPUnit\Framework\TestCase;

final class WrappedResponseDecoderTest extends TestCase
{
    private WrappedResponseDecoder $decoder;

    protected function setUp(): void
    {
        $this->decoder = new WrappedResponseDecoder();
    }

    public function testUnwrapsSuccessEnvelope(): void
    {
        $response = new Response(200, [], json_encode([
            'success' => true,
            'data' => ['id' => 1, 'name' => 'Test'],
        ]));

        $this->assertSame(['id' => 1, 'name' => 'Test'], $this->decoder->decode($response));
    }

    public function testReturnsEmptyArrayWhenDataKeyMissing(): void
    {
        $response = new Response(200, [], json_encode(['success' => true]));

        $this->assertSame([], $this->decoder->decode($response));
    }

    public function testThrowsHttpExceptionWithMessages(): void
    {
        $response = new Response(200, [], json_encode([
            'success' => false,
            'error' => ['messages' => ['Validation failed', 'Name required']],
        ]));

        $this->expectException(HttpException::class);
        $this->expectExceptionMessage('Validation failed, Name required');
        $this->decoder->decode($response);
    }

    public function testThrowsHttpExceptionWithFallbackMessage(): void
    {
        $response = new Response(200, [], json_encode(['success' => false]));

        $this->expectException(HttpException::class);
        $this->expectExceptionMessage('API error');
        $this->decoder->decode($response);
    }

    public function testThrowsUnauthorizedOn401(): void
    {
        $this->expectException(UnauthorizedException::class);
        $this->decoder->decode(new Response(401, [], ''));
    }

    public function testThrowsServerExceptionOn5xx(): void
    {
        $this->expectException(ServerException::class);
        $this->decoder->decode(new Response(500, [], 'error'));
    }
}
