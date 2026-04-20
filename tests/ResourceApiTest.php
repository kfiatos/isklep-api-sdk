<?php

declare(strict_types=1);

namespace ISklep\Api\Tests;

use GuzzleHttp\Client as GuzzleHttpClient;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use ISklep\Api\Authorisation\AuthorisationInterface;
use ISklep\Api\Client;
use ISklep\Api\Exceptions\ApiException;
use ISklep\Api\Http\GuzzleHttpClientFactory;
use ISklep\Api\Http\Operation;
use ISklep\Api\JsonResponseDecoder;
use ISklep\Api\Models\Producer;
use ISklep\Api\ResourceApi;
use PHPUnit\Framework\TestCase;

final class ResourceApiTest extends TestCase
{
    private function createApi(MockHandler $mockHandler): ResourceApi
    {
        $auth = $this->createMock(AuthorisationInterface::class);
        $auth->method('authorise')->willReturnArgument(0);

        $client = new Client(
            new GuzzleHttpClientFactory(new GuzzleHttpClient(['handler' => HandlerStack::create($mockHandler)])),
            $auth,
        );

        return new class ($client, new JsonResponseDecoder()) extends ResourceApi {
            protected function getModelClass(): string
            {
                return Producer::class;
            }

            protected function getOperations(): array
            {
                return [
                    Operation::List->value => '/producers',
                    Operation::Get->value => '/producers/{id}',
                    Operation::Create->value => '/producers',
                    Operation::Update->value => '/producers/{id}',
                    Operation::Delete->value => '/producers/{id}',
                ];
            }
        };
    }

    public function testGetReturnsModel(): void
    {
        $mock = new MockHandler([new Response(200, [], json_encode(['id' => 5, 'name' => 'Fetched']))]);
        $result = $this->createApi($mock)->get(5);

        $this->assertInstanceOf(Producer::class, $result);
        $this->assertSame(5, $result->id);
        $this->assertSame('Fetched', $result->name);
    }

    public function testUpdateReturnsUpdatedModel(): void
    {
        $mock = new MockHandler([new Response(200, [], json_encode(['id' => 5, 'name' => 'Updated']))]);
        $result = $this->createApi($mock)->update(5, new Producer(id: 5, name: 'Updated'));

        $this->assertInstanceOf(Producer::class, $result);
        $this->assertSame('Updated', $result->name);
    }

    public function testDeleteCompletesWithoutException(): void
    {
        $mock = new MockHandler([new Response(204, [], '')]);
        $this->createApi($mock)->delete(5);
        $this->addToAssertionCount(1);
    }

    public function testCreateWrapsBodyInResourceKey(): void
    {
        $mock = new MockHandler([
            function ($request) {
                $body = json_decode((string) $request->getBody(), true);
                $this->assertArrayHasKey('producer', $body);
                $this->assertSame('New', $body['producer']['name']);

                return new Response(201, [], json_encode(['id' => 99, 'name' => 'New']));
            },
        ]);

        $this->createApi($mock)->create(new Producer(id: 99, name: 'New'));
    }

    public function testUpdateWrapsBodyInResourceKey(): void
    {
        $mock = new MockHandler([
            function ($request) {
                $body = json_decode((string) $request->getBody(), true);
                $this->assertArrayHasKey('producer', $body);

                return new Response(200, [], json_encode(['id' => 5, 'name' => 'X']));
            },
        ]);

        $this->createApi($mock)->update(5, new Producer(id: 5, name: 'X'));
    }

    public function testUnsupportedOperationThrowsApiException(): void
    {
        $auth = $this->createMock(AuthorisationInterface::class);
        $client = new Client(
            new GuzzleHttpClientFactory(new GuzzleHttpClient(['handler' => HandlerStack::create(new MockHandler())])),
            $auth,
        );

        $api = new class ($client, new JsonResponseDecoder()) extends ResourceApi {
            protected function getModelClass(): string
            {
                return Producer::class;
            }

            protected function getOperations(): array
            {
                return [];
            }
        };

        $this->expectException(ApiException::class);
        $api->list();
    }
}
