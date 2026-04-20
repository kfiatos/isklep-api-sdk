<?php

declare(strict_types=1);

namespace ISklep\Api\Tests\Models;

use ISklep\Api\Models\Producer;
use PHPUnit\Framework\TestCase;

final class ProducerTest extends TestCase
{
    public function testFromArrayMapsAllFields(): void
    {
        $producer = Producer::fromArray([
            'id' => 7,
            'name' => 'Acme',
            'site_url' => 'http://example.com',
            'logo_filename' => 'acme.png',
            'ordering' => 3,
            'source_id' => 'SRC-7',
        ]);

        $this->assertSame(7, $producer->id);
        $this->assertSame('Acme', $producer->name);
        $this->assertSame('http://example.com', $producer->siteUrl);
        $this->assertSame('acme.png', $producer->logoFilename);
        $this->assertSame(3, $producer->ordering);
        $this->assertSame('SRC-7', $producer->sourceId);
    }

    public function testToArrayIncludesNullFields(): void
    {
        $producer = new Producer(id: null, name: 'X');
        $array = $producer->toArray();

        $this->assertArrayHasKey('id', $array);
        $this->assertNull($array['id']);
        $this->assertSame('X', $array['name']);
    }

    public function testJsonSerializeFiltersNullFields(): void
    {
        $producer = new Producer(id: 1, name: 'X');
        $serialized = $producer->jsonSerialize();

        $this->assertSame(1, $serialized['id']);
        $this->assertSame('X', $serialized['name']);
        $this->assertArrayNotHasKey('site_url', $serialized);
        $this->assertArrayNotHasKey('logo_filename', $serialized);
        $this->assertArrayNotHasKey('ordering', $serialized);
        $this->assertArrayNotHasKey('source_id', $serialized);
    }

    public function testGetResourceKey(): void
    {
        $this->assertSame('producer', Producer::getResourceKey());
    }
}
