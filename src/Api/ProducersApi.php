<?php

declare(strict_types=1);

namespace ISklep\Api\Api;

use ISklep\Api\Http\Operation;
use ISklep\Api\Models\Producer;
use ISklep\Api\ResourceApi;

/**
 * @extends ResourceApi<Producer>
 */
final class ProducersApi extends ResourceApi
{
    protected function getModelClass(): string
    {
        return Producer::class;
    }

    protected function getOperations(): array
    {
        return [
            Operation::List->value => '/shop_api/v1/producers',
            Operation::Create->value => '/shop_api/v1/producers',
            /* May be needed in the future but for now only list and create was needed
            Operation::Get->value => '/shop_api/v1/producers/{id}',
            Operation::Update->value => '/shop_api/v1/producers/{id}',
            Operation::Delete->value => '/shop_api/v1/producers/{id}',
        */
        ];
    }
}
