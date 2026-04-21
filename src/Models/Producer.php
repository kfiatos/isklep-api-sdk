<?php

declare(strict_types=1);

namespace ISklep\Api\Models;

use ISklep\Api\Exceptions\DeserializationException;

final class Producer extends AbstractModel
{
    public static function getResourceKey(): string
    {
        return 'producer';
    }

    public function __construct(
        public readonly int|string|null $id,
        public readonly string $name,
        public readonly ?string $siteUrl = null,
        public readonly ?string $logoFilename = null,
        public readonly ?int $ordering = null,
        public readonly ?string $sourceId = null,
    ) {}

    /**
     * @param array<string, mixed> $data
     * @return self
     *
     * @throws DeserializationException
     */
    public static function fromArray(array $data): self
    {
        if (!isset($data['name']) || $data['name'] === '') {
            throw new DeserializationException('Producer name is required');
        }


        return new self(
            id: $data['id'] ?? null,
            name: $data['name'],
            siteUrl: $data['site_url'] ?? null,
            logoFilename: $data['logo_filename'] ?? null,
            ordering: isset($data['ordering']) ? (int) $data['ordering'] : null,
            sourceId: $data['source_id'] ?? null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'site_url' => $this->siteUrl,
            'logo_filename' => $this->logoFilename,
            'ordering' => $this->ordering,
            'source_id' => $this->sourceId,
        ];
    }
}
