<?php

declare(strict_types=1);

use GuzzleHttp\Promise\Utils;
use HubSpot\Exceptions\ApiException;
use HubSpot\Exceptions\RateLimitException;

it('requestAsync() resolves to the decoded body', function () {
    [$client] = mockClient([jsonResponse(200, ['id' => '1', 'ok' => true])]);

    $result = $client->requestAsync('GET', '/crm/objects/2026-09/contacts/1')->wait();

    expect($result)->toBe(['id' => '1', 'ok' => true]);
});

it('runs several requests concurrently and preserves order', function () {
    [$client] = mockClient([
        jsonResponse(200, ['n' => 1]),
        jsonResponse(200, ['n' => 2]),
        jsonResponse(200, ['n' => 3]),
    ]);

    $results = Utils::all([
        $client->requestAsync('GET', '/a'),
        $client->requestAsync('GET', '/b'),
        $client->requestAsync('GET', '/c'),
    ])->wait();

    expect($results)->toBe([['n' => 1], ['n' => 2], ['n' => 3]]);
});

it('sendAsync() rejects with a typed exception on a 4xx', function () {
    [$client] = mockClient([jsonResponse(400, ['message' => 'Property values were not valid'])]);

    expect(fn () => $client->sendAsync('POST', '/crm/objects/2026-09/contacts')->wait())
        ->toThrow(ApiException::class);
});

it('settle() lets a mix of success and failure be inspected per request', function () {
    [$client] = mockClient([
        jsonResponse(202, ['status' => 'ok']),
        jsonResponse(429, ['message' => 'slow down']),
    ], maxRetries: 0);

    $settled = Utils::settle([
        $client->sendAsync('POST', '/integrators/timeline/2026-09/events/batch'),
        $client->sendAsync('POST', '/integrators/timeline/2026-09/events/batch'),
    ])->wait();

    expect($settled[0]['state'])->toBe('fulfilled');
    expect($settled[0]['value']->getStatusCode())->toBe(202);
    expect($settled[1]['state'])->toBe('rejected');
    expect($settled[1]['reason'])->toBeInstanceOf(RateLimitException::class);
});

it('pool() runs thunks with capped concurrency and keys the outcomes', function () {
    [$client] = mockClient([
        jsonResponse(200, ['n' => 1]),
        jsonResponse(400, ['message' => 'bad']),
        jsonResponse(200, ['n' => 3]),
    ], maxRetries: 0);

    $results = $client->pool([
        'a' => fn () => $client->requestAsync('GET', '/a'),
        'b' => fn () => $client->sendAsync('GET', '/b'),
        'c' => fn () => $client->requestAsync('GET', '/c'),
    ], concurrency: 2);

    expect($results['a'])->toBe(['n' => 1]);
    expect($results['b'])->toBeInstanceOf(ApiException::class);
    expect($results['c'])->toBe(['n' => 3]);
});

it('timeline createBatchAsync() posts the batch path and resolves', function () {
    [$client, $mock] = mockClient([jsonResponse(202, ['completedAt' => '2026-09-23'])]);

    $result = $client->crm()->timeline()->createBatchAsync(['eventTemplateId' => 't', 'events' => []])->wait();

    expect($mock->getLastRequest()->getMethod())->toBe('POST');
    expect($mock->getLastRequest()->getUri()->getPath())->toBe('/integrators/timeline/2026-09/events/batch');
    expect($result)->toBe(['completedAt' => '2026-09-23']);
});
