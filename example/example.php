<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use GuzzleHttp\Client as GuzzleHttpClient;
use ISklep\Api\Api\ProducersApi;
use ISklep\Api\Authorisation\BasicAuthorisation;
use ISklep\Api\Client;
use ISklep\Api\Exceptions\ApiException;
use ISklep\Api\Http\GuzzleHttpAdapter;
use ISklep\Api\JsonResponseDecoder;
use ISklep\Api\Models\Producer;

$baseUrl = 'http://nginx';
$username = ''; //fill with proper credentials
$password = '!'; //fill with proper credentials

$authorisation = new BasicAuthorisation($username, $password);
$guzzleClient = new GuzzleHttpClient(['http_errors' => false]);
$httpClientFactory = new GuzzleHttpAdapter($guzzleClient);

$client = new Client(
    httpClient: $httpClientFactory,
    authorisation: $authorisation,
    baseUri: $baseUrl,
);

$client = $client->withHeader('Host', 'rekrutacja.localhost');

$producersApi = new ProducersApi($client, new JsonResponseDecoder());

try {
    echo "Get current list from api" . PHP_EOL;
    $producers = $producersApi->list();

    echo "Found " . count($producers) . " producers:" . PHP_EOL;
    foreach ($producers as $producer) {
        echo "- [{$producer->id}] {$producer->name} (Logo: {$producer->logoFilename})" . PHP_EOL;
    }

    echo PHP_EOL . "Create new Producer with needed data" . PHP_EOL;
    $newProducerData = new Producer(
        id: rand(100, 1000),
        name: 'New Producer ' . uniqid(),
        siteUrl: 'http://example.com',
        logoFilename: 'logo.png',
        ordering: 10,
        sourceId: 'EXT-' . rand(100, 999),
    );

    $createdProducer = $producersApi->create($newProducerData);
    echo "Created producer ID: {$createdProducer->id}, Name: {$createdProducer->name}" . PHP_EOL;

} catch (ApiException $e) {
    echo "Error [{$e->getStatusCode()}]: " . $e->getMessage() . "\n";
    echo "Body: " . $e->getResponseBody() . "\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
