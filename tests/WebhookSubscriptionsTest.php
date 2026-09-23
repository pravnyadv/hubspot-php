<?php

declare(strict_types=1);

it('getSettings() sends GET /app-webhooks/2026-09/{appId}/settings', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['targetUrl' => 'https://example.com/hook', 'active' => true]),
    ]);

    $result = $client->settings()->webhooks()->getSettings(123456);

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('GET');
    expect($request->getUri()->getPath())->toBe('/app-webhooks/2026-09/123456/settings');
    expect($result)->toBe(['targetUrl' => 'https://example.com/hook', 'active' => true]);
});

it('updateSettings() sends PUT /app-webhooks/2026-09/{appId}/settings with body', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['targetUrl' => 'https://example.com/new', 'active' => true]),
    ]);

    $result = $client->settings()->webhooks()->updateSettings(123456, ['targetUrl' => 'https://example.com/new']);

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('PUT');
    expect($request->getUri()->getPath())->toBe('/app-webhooks/2026-09/123456/settings');
    expect(json_decode((string) $request->getBody(), true))->toBe(['targetUrl' => 'https://example.com/new']);
    expect($result)->toBe(['targetUrl' => 'https://example.com/new', 'active' => true]);
});

it('list() sends GET /app-webhooks/2026-09/{appId}/subscriptions', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['results' => [['id' => '1', 'eventType' => 'contact.creation']]]),
    ]);

    $result = $client->settings()->webhooks()->list(123456);

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('GET');
    expect($request->getUri()->getPath())->toBe('/app-webhooks/2026-09/123456/subscriptions');
    expect($result)->toBe(['results' => [['id' => '1', 'eventType' => 'contact.creation']]]);
});

it('get() sends GET /app-webhooks/2026-09/{appId}/subscriptions/{subscriptionId}', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['id' => '42', 'eventType' => 'contact.propertyChange']),
    ]);

    $result = $client->settings()->webhooks()->get(123456, '42');

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('GET');
    expect($request->getUri()->getPath())->toBe('/app-webhooks/2026-09/123456/subscriptions/42');
    expect($result)->toBe(['id' => '42', 'eventType' => 'contact.propertyChange']);
});

it('create() sends POST /app-webhooks/2026-09/{appId}/subscriptions with body', function () {
    [$client, $mock] = mockClient([
        jsonResponse(201, ['id' => '99', 'eventType' => 'deal.creation']),
    ]);

    $result = $client->settings()->webhooks()->create(123456, ['eventType' => 'deal.creation', 'active' => true]);

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('POST');
    expect($request->getUri()->getPath())->toBe('/app-webhooks/2026-09/123456/subscriptions');
    expect(json_decode((string) $request->getBody(), true))->toBe(['eventType' => 'deal.creation', 'active' => true]);
    expect($result)->toBe(['id' => '99', 'eventType' => 'deal.creation']);
});

it('update() sends PATCH /app-webhooks/2026-09/{appId}/subscriptions/{subscriptionId}', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['id' => '42', 'active' => false]),
    ]);

    $result = $client->settings()->webhooks()->update(123456, '42', ['active' => false]);

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('PATCH');
    expect($request->getUri()->getPath())->toBe('/app-webhooks/2026-09/123456/subscriptions/42');
    expect(json_decode((string) $request->getBody(), true))->toBe(['active' => false]);
    expect($result)->toBe(['id' => '42', 'active' => false]);
});

it('archive() sends DELETE /app-webhooks/2026-09/{appId}/subscriptions/{subscriptionId}', function () {
    [$client, $mock] = mockClient([
        jsonResponse(204),
    ]);

    $client->settings()->webhooks()->archive(123456, '42');

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('DELETE');
    expect($request->getUri()->getPath())->toBe('/app-webhooks/2026-09/123456/subscriptions/42');
});

it('batchUpdate() sends POST /app-webhooks/2026-09/{appId}/subscriptions/batch/update with inputs', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['results' => [['id' => '1', 'active' => false]]]),
    ]);

    $inputs = [['id' => '1', 'active' => false]];
    $result = $client->settings()->webhooks()->batchUpdate(123456, $inputs);

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('POST');
    expect($request->getUri()->getPath())->toBe('/app-webhooks/2026-09/123456/subscriptions/batch/update');
    expect(json_decode((string) $request->getBody(), true))->toBe(['inputs' => $inputs]);
    expect($result)->toBe(['results' => [['id' => '1', 'active' => false]]]);
});

it('version override changes the date segment in the path', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['results' => []]),
    ]);

    $client->settings()->webhooks('2026-03')->list(123456);

    expect($mock->getLastRequest()->getUri()->getPath())->toBe('/app-webhooks/2026-03/123456/subscriptions');
});
