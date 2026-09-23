<?php

declare(strict_types=1);

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use HubSpot\Auth\AccessTokenAuth;
use HubSpot\Client;
use HubSpot\ResponseFormat;

/** Build a client with the real production default (no format passed). */
function defaultClient(array $responses): array
{
    $mock = new MockHandler($responses);
    $http = new GuzzleClient([
        'base_uri' => 'https://api.hubapi.com',
        'handler' => HandlerStack::create($mock),
        'http_errors' => false,
    ]);

    return [new Client(new AccessTokenAuth('t'), $http), $mock];
}

it('defaults to Object: a fresh client returns stdClass read via arrow access', function () {
    [$client] = defaultClient([jsonResponse(200, ['properties' => ['email' => 'a@b.com']])]);

    $contact = $client->crm()->objects('contacts')->get('1');

    expect($contact)->toBeInstanceOf(stdClass::class);
    expect($contact->properties->email)->toBe('a@b.com');
});

it('returns associative arrays in Assoc mode', function () {
    [$client] = mockClient([jsonResponse(200, ['properties' => ['email' => 'a@b.com']])], format: ResponseFormat::Assoc);

    $contact = $client->crm()->objects('contacts')->get('1');

    expect($contact)->toBeArray();
    expect($contact['properties']['email'])->toBe('a@b.com');
});

it('honours a per-call format override on the raw request escape hatch', function () {
    [$client] = mockClient([jsonResponse(200, ['ok' => true])], format: ResponseFormat::Assoc);

    $asObject = $client->request('GET', '/account-info/2026-09/details', [], ResponseFormat::Object);

    expect($asObject)->toBeInstanceOf(stdClass::class);
    expect($asObject->ok)->toBeTrue();
});

it('paginates correctly in Object mode', function () {
    [$client] = mockClient([
        jsonResponse(200, ['results' => [['id' => '1'], ['id' => '2']], 'paging' => ['next' => ['after' => 'p2']]]),
        jsonResponse(200, ['results' => [['id' => '3']]]),
    ], format: ResponseFormat::Object);

    $ids = [];
    foreach ($client->crm()->owners()->all() as $owner) {
        $ids[] = $owner->id;
    }

    expect($ids)->toBe(['1', '2', '3']);
});

it('reports its configured response format', function () {
    [$client] = defaultClient([]);
    expect($client->responseFormat())->toBe(ResponseFormat::Object);
});
