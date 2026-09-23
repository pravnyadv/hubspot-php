<?php

declare(strict_types=1);

it('definitions() sends GET /communication-preferences/2026-09/definitions', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['subscriptionDefinitions' => [['id' => 'def-1']]]),
    ]);

    $result = $client->marketing()->subscriptions()->definitions();

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('GET');
    expect($request->getUri()->getPath())->toBe('/communication-preferences/2026-09/definitions');
    expect($result)->toBe(['subscriptionDefinitions' => [['id' => 'def-1']]]);
});

it('statuses() sends GET /communication-preferences/2026-09/statuses/{subscriberId}', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['subscriptionStatuses' => [['id' => 'sub-1', 'subscribed' => true]]]),
    ]);

    $result = $client->marketing()->subscriptions()->statuses('contact@example.com');

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('GET');
    expect($request->getUri()->getPath())
        ->toBe('/communication-preferences/2026-09/statuses/contact@example.com');
    expect($result)->toBe(['subscriptionStatuses' => [['id' => 'sub-1', 'subscribed' => true]]]);
});

it('batchWrite() sends POST /communication-preferences/2026-09/statuses/batch/write with inputs body', function () {
    $inputs = [
        ['emailAddress' => 'a@example.com', 'subscriptionId' => '1', 'legalBasis' => 'LEGITIMATE_INTEREST_PQL'],
        ['emailAddress' => 'b@example.com', 'subscriptionId' => '2', 'legalBasis' => 'LEGITIMATE_INTEREST_PQL'],
    ];

    [$client, $mock] = mockClient([
        jsonResponse(200, ['status' => 'COMPLETE', 'results' => []]),
    ]);

    $result = $client->marketing()->subscriptions()->batchWrite($inputs);

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('POST');
    expect($request->getUri()->getPath())
        ->toBe('/communication-preferences/2026-09/statuses/batch/write');

    $body = json_decode((string) $request->getBody(), true);
    expect($body)->toHaveKey('inputs');
    expect($body['inputs'])->toBe($inputs);

    expect($result)->toBe(['status' => 'COMPLETE', 'results' => []]);
});

it('unsubscribeAll() sends POST /communication-preferences/2026-09/statuses/{subscriberId}/unsubscribe-all', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['status' => 'COMPLETE']),
    ]);

    $result = $client->marketing()->subscriptions()->unsubscribeAll('contact@example.com');

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('POST');
    expect($request->getUri()->getPath())
        ->toBe('/communication-preferences/2026-09/statuses/contact@example.com/unsubscribe-all');
    expect($result)->toBe(['status' => 'COMPLETE']);
});

it('version override changes the date segment in the path', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['subscriptionDefinitions' => []]),
    ]);

    $client->marketing()->subscriptions('2026-03')->definitions();

    expect($mock->getLastRequest()->getUri()->getPath())
        ->toBe('/communication-preferences/2026-03/definitions');
});
