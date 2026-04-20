<?php

declare(strict_types=1);

namespace ISklep\Api\Models;

use JsonSerializable;

abstract class AbstractModel implements JsonSerializable
{
    abstract public static function getResourceKey(): string;

    /**
     * @param array<string, mixed> $data
     */
    abstract public static function fromArray(array $data): self;

    /**
     * @return array<string, mixed>
     */
    abstract public function toArray(): array;

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return array_filter($this->toArray(), static fn(mixed $value): bool => $value !== null);
    }
}
