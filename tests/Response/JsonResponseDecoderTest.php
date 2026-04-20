<?php

declare(strict_types=1);

namespace ISklep\Api\Tests\Response;

use GuzzleHttp\Psr7\Response;
use ISklep\Api\Exceptions\ClientException;
use ISklep\Api\Exceptions\DeserializationException;
use ISklep\Api\Exceptions\ServerException;
use ISklep\Api\Exceptions\UnauthorizedException;
use ISklep\Api\JsonResponseDecoder;
use PHPUnit\Framework\TestCase;

final class JsonResponseDecoderTest extends TestCase
{
    private JsonResponseDecoder $decoder;

    protected function setUp(): void
    {
        $this->decoder = new JsonResponseDecoder();
    }

    public function testDecodesJsonObject(): void
    {
        $response = new Response(200, [], json_encode(['id' => 1, 'name' => 'A']));

        $this->assertSame(['id' => 1, 'name' => 'A'], $this->decoder->decode($response));
    }

    public function testDecodesJsonArray(): void
    {
        $response = new Response(200, [], json_encode([['id' => 1], ['id' => 2]]));

        $this->assertSame([['id' => 1], ['id' => 2]], $this->decoder->decode($response));
    }

    public function testReturnsEmptyArrayForEmptyBody(): void
    {
        $this->assertSame([], $this->decoder->decode(new Response(200, [], '')));
    }

    public function testThrowsUnauthorizedOn401(): void
    {
        $this->expectException(UnauthorizedException::class);
        $this->decoder->decode(new Response(401, [], 'Unauthorized'));
    }

    public function testThrowsServerExceptionOn5xx(): void
    {
        $this->expectException(ServerException::class);
        $this->decoder->decode(new Response(500, [], 'error'));
    }

    public function testThrowsClientExceptionOn4xx(): void
    {
        $this->expectException(ClientException::class);
        $this->decoder->decode(new Response(404, [], 'not found'));
    }

    public function testThrowsDeserializationExceptionOnInvalidJson(): void
    {
        $this->expectException(DeserializationException::class);
        $this->decoder->decode(new Response(200, [], 'not-json'));
    }
}
