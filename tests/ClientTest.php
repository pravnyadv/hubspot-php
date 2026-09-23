<?php

declare(strict_types=1);

use HubSpot\Exceptions\ApiException;
use HubSpot\Exceptions\RateLimitException;

it('attaches the bearer token and returns a decoded array', function () {
    [$client, $mock] = mockClient([jsonResponse(200, ['id' => '1', 'ok' => true])]);

    $result = $client->request('GET', '/crm/objects/2026-09/contacts/1');

    expect($result)->toBe(['id' => '1', 'ok' => true]);
    expect($mock->getLastRequest()->getHeaderLine('Authorization'))->toBe('Bearer test-token');
    expect($mock->getLastRequest()->getUri()->getPath())->toBe('/crm/objects/2026-09/contacts/1');
});

it('maps a 429 to a RateLimitException carrying Retry-After and status', function () {
    [$client] = mockClient([jsonResponse(429, ['message' => 'slow down'], ['Retry-After' => '7'])], maxRetries: 0);

    try {
        $client->request('GET', '/crm/owners/2026-09');
        expect(false)->toBeTrue('expected RateLimitException');
    } catch (RateLimitException $e) {
        expect($e->retryAfter)->toBe(7);
        expect($e->status)->toBe(429);
    }
});

it('extracts the first field error from a HubSpot validation message', function () {
    $body = [
        'status' => 'error',
        'message' => 'Property values were not valid: [{"isValid":false,"message":"Email is invalid","error":"INVALID_EMAIL","name":"email"}]',
        'category' => 'VALIDATION_ERROR',
    ];
    [$client] = mockClient([jsonResponse(400, $body)]);

    try {
        $client->request('POST', '/crm/objects/2026-09/contacts', ['json' => ['properties' => []]]);
        expect(false)->toBeTrue('expected ApiException');
    } catch (ApiException $e) {
        expect($e->getMessage())->toBe('Email is invalid');
        expect($e->status)->toBe(400);
        expect($e->body)->toBe($body);
    }
});

it('retries on 500 then succeeds', function () {
    [$client, $mock] = mockClient([
        jsonResponse(500, ['message' => 'boom']),
        jsonResponse(200, ['recovered' => true]),
    ]);

    $result = $client->request('GET', '/account-info/2026-09/details');

    expect($result)->toBe(['recovered' => true]);
    expect($mock->count())->toBe(0);
});

it('gives up after exhausting retries', function () {
    [$client] = mockClient([
        jsonResponse(503),
        jsonResponse(503),
    ], maxRetries: 1);

    expect(fn () => $client->request('GET', '/account-info/2026-09/details'))
        ->toThrow(ApiException::class);
});

it('does NOT retry a POST on a 5xx, to avoid duplicate writes', function () {
    [$client, $mock] = mockClient([
        jsonResponse(500, ['message' => 'boom']),
        jsonResponse(200, ['recovered' => true]),
    ]);

    expect(fn () => $client->request('POST', '/crm/objects/2026-09/contacts', ['json' => []]))
        ->toThrow(ApiException::class);
    // The second response is untouched: the POST was not retried.
    expect($mock->count())->toBe(1);
});

it('does retry a POST on a 429, which is safe (not processed)', function () {
    [$client, $mock] = mockClient([
        jsonResponse(429, ['message' => 'slow down']),
        jsonResponse(200, ['id' => '1']),
    ]);

    $result = $client->request('POST', '/crm/objects/2026-09/contacts', ['json' => []]);

    expect($result)->toBe(['id' => '1']);
    expect($mock->count())->toBe(0);
});
