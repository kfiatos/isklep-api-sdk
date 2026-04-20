<?php

declare(strict_types=1);

namespace ISklep\Api\Tests;

use GuzzleHttp\Client as GuzzleHttpClient;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use ISklep\Api\Api\ProducersApi;
use ISklep\Api\Authorisation\AuthorisationInterface;
use ISklep\Api\Client;
use ISklep\Api\Exceptions\HttpException;
use ISklep\Api\Exceptions\UnauthorizedException;
use ISklep\Api\Http\GuzzleHttpClientFactory;
use ISklep\Api\Models\Producer;
use PHPUnit\Framework\TestCase;

class ClientTest extends TestCase
{
    private function createClient(MockHandler $mockHandler, ?AuthorisationInterface $authorisation = null): Client
    {
        $guzzleClient = new GuzzleHttpClient(['handler' => HandlerStack::create($mockHandler)]);
        $httpClientFactory = new GuzzleHttpClientFactory($guzzleClient);

        if ($authorisation === null) {
            $authorisation = $this->createMock(AuthorisationInterface::class);
            $authorisation->method('authorise')->willReturnArgument(0);
        }

        return new Client($httpClientFactory, $authorisation);
    }

    public function testProducersApiList(): void
    {
        $responseData = [
            'success' => true,
            'data' => [
                [
                    'id' => 1,
                    'name' => 'Test Producer',
                    'site_url' => 'http://test.com',
                    'logo_filename' => 'test.png',
                    'ordering' => 0,
                    'source_id' => 'src-1',
                ],
            ],
        ];

        $mock = new MockHandler([new Response(200, [], json_encode($responseData))]);
        $api = new ProducersApi($this->createClient($mock));
        $result = $api->list();

        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertInstanceOf(Producer::class, $result[0]);
        $this->assertEquals(1, $result[0]->id);
        $this->assertEquals('Test Producer', $result[0]->name);
    }

    public function testProducersApiCreate(): void
    {
        $responseData = [
            'success' => true,
            'data' => [
                'id' => 42,
                'name' => 'New Producer',
                'site_url' => 'http://producer.example.com',
                'logo_filename' => 'producer.png',
                'ordering' => 10,
                'source_id' => 'src-new',
            ],
        ];

        $mock = new MockHandler([new Response(201, [], json_encode($responseData))]);
        $api = new ProducersApi($this->createClient($mock));

        $result = $api->create(new Producer(
            id: null,
            name: 'New Producer',
            siteUrl: 'http://producer.example.com',
            logoFilename: 'producer.png',
            ordering: 10,
            sourceId: 'src-new',
        ));

        $this->assertInstanceOf(Producer::class, $result);
        $this->assertEquals(42, $result->id);
        $this->assertEquals('New Producer', $result->name);
    }

    public function testThrowsUnauthorizedException(): void
    {
        $mock = new MockHandler([new Response(401, [], '{"error": "Unauthorized"}')]);
        $api = new ProducersApi($this->createClient($mock));

        $this->expectException(UnauthorizedException::class);
        $api->list();
    }

    public function testThrowsHttpExceptionWithDetails(): void
    {
        $responseBody = json_encode([
            'success' => false,
            'error' => [
                'reason_code' => 'INVALID_DATA',
                'messages' => ['Invalid ID'],
            ],
        ]);
        $mock = new MockHandler([new Response(400, [], (string) $responseBody)]);
        $api = new ProducersApi($this->createClient($mock));

        try {
            $api->list();
            $this->fail('Expected HttpException was not thrown.');
        } catch (HttpException $e) {
            $this->assertEquals(400, $e->getStatusCode());
            $this->assertEquals('Invalid ID', $e->getMessage());
        }
    }

    public function testGlobalHeaderIsApplied(): void
    {
        $mock = new MockHandler([
            function ($request) {
                $this->assertEquals('rekrutacja.localhost', $request->getHeaderLine('Host'));
                return new Response(200, [], json_encode(['success' => true, 'data' => []]));
            },
        ]);

        $client = $this->createClient($mock)->withHeader('Host', 'rekrutacja.localhost');
        (new ProducersApi($client))->list();
    }
}
