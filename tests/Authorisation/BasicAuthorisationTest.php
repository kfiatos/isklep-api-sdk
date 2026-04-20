<?php

declare(strict_types=1);

namespace ISklep\Api\Tests\Authorisation;

use GuzzleHttp\Psr7\Request;
use ISklep\Api\Authorisation\BasicAuthorisation;
use PHPUnit\Framework\TestCase;

final class BasicAuthorisationTest extends TestCase
{
    public function testAddsBasicAuthorizationHeader(): void
    {
        $auth = new BasicAuthorisation('user', 's3cr3t');
        $result = $auth->authorise(new Request('GET', 'http://example.com'));

        $this->assertSame(
            'Basic ' . base64_encode('user:s3cr3t'),
            $result->getHeaderLine('Authorization'),
        );
    }
}
