<?php

declare(strict_types=1);

namespace ISklep\Api;

use ISklep\Api\Exceptions\ApiException;
use ISklep\Api\Http\Operation;
use ISklep\Api\Models\AbstractModel;
use Psr\Http\Message\ResponseInterface;

/**
 * @template T of AbstractModel
 */
abstract class ResourceApi
{
    public function __construct(
        protected readonly ApiClientInterface $client,
        protected readonly ResponseDecoderInterface $decoder = new WrappedResponseDecoder(),
    ) {}

    /**
     * @return class-string<T>
     */
    abstract protected function getModelClass(): string;

    /**
     * @return array<string, string> Operation value => URI path mapping
     */
    abstract protected function getOperations(): array;

    /**
     * @param array<string, mixed> $params
     * @return list<T>
     *
     * @throws ApiException
     */
    public function list(array $params = []): array
    {
        $uri = $this->resolveUri(Operation::List->value);
        $items = $this->decode($this->client->get($uri, $params));
        $class = $this->getModelClass();

        /** @var list<T> */
        return array_values(array_map(
            static fn(array $item): AbstractModel => $class::fromArray($item),
            $items,
        ));
    }

    /**
     * @param string|int $id
     * @return T
     *
     * @throws ApiException
     */
    public function get(string|int $id): AbstractModel
    {
        $uri = $this->resolveUri(Operation::Get->value, ['id' => $id]);
        $data = $this->decode($this->client->get($uri));
        $class = $this->getModelClass();

        /** @var T */
        return $class::fromArray($data);
    }

    /**
     * @param T $model
     * @return T
     *
     * @throws ApiException
     */
    public function create(AbstractModel $model): AbstractModel
    {
        $uri = $this->resolveUri(Operation::Create->value);
        $class = $this->getModelClass();
        $data = $this->decode($this->client->post($uri, [$class::getResourceKey() => $model->toArray()]));

        /** @var T */
        return $class::fromArray($data);
    }

    /**
     * @param string|int $id
     * @param T $model
     * @return T
     *
     * @throws ApiException
     */
    public function update(string|int $id, AbstractModel $model): AbstractModel
    {
        $uri = $this->resolveUri(Operation::Update->value, ['id' => $id]);
        $class = $this->getModelClass();
        $data = $this->decode($this->client->put($uri, [$class::getResourceKey() => $model->toArray()]));

        /** @var T */
        return $class::fromArray($data);
    }

    /**
     * @param string|int $id
     *
     * @throws ApiException
     */
    public function delete(string|int $id): void
    {
        $uri = $this->resolveUri(Operation::Delete->value, ['id' => $id]);
        $this->decode($this->client->delete($uri));
    }

    /**
     * @return array<string, mixed>
     *
     * @throws ApiException
     */
    protected function decode(ResponseInterface $response): array
    {
        return $this->decoder->decode($response);
    }

    /**
     * @param array<string, int|string> $params
     */
    protected function resolveUri(string $operation, array $params = []): string
    {
        $operations = $this->getOperations();

        if (!isset($operations[$operation])) {
            throw new ApiException("Operation {$operation} not supported for this resource");
        }

        $uri = $operations[$operation];

        foreach ($params as $key => $value) {
            $uri = str_replace('{' . $key . '}', (string) $value, $uri);
        }

        return $uri;
    }
}
