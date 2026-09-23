<?php

declare(strict_types=1);

it('flows() sends GET /automation/2026-09-beta/flows by default', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['results' => [['id' => 'flow-1']]]),
    ]);

    $result = $client->automation()->flows()->list();

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('GET');
    expect($request->getUri()->getPath())->toBe('/automation/2026-09-beta/flows');
    expect($result)->toBe(['results' => [['id' => 'flow-1']]]);
});

it('flows() passes query params and omits null values', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['results' => []]),
    ]);

    $client->automation()->flows()->list(['limit' => 10, 'after' => null]);

    parse_str($mock->getLastRequest()->getUri()->getQuery(), $query);
    expect($query['limit'])->toBe('10');
    expect($query)->not->toHaveKey('after');
});

it('flow() sends GET /automation/2026-09-beta/flows/{flowId}', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['id' => 'flow-abc', 'name' => 'My Flow']),
    ]);

    $result = $client->automation()->flows()->get('flow-abc');

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('GET');
    expect($request->getUri()->getPath())->toBe('/automation/2026-09-beta/flows/flow-abc');
    expect($result)->toBe(['id' => 'flow-abc', 'name' => 'My Flow']);
});

it('flowsBatchRead() sends POST /automation/2026-09-beta/flows/batch/read with inputs in the body', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['results' => [['id' => 'flow-1'], ['id' => 'flow-2']]]),
    ]);

    $inputs = [['id' => 'flow-1'], ['id' => 'flow-2']];
    $result = $client->automation()->flows()->batchRead($inputs);

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('POST');
    expect($request->getUri()->getPath())->toBe('/automation/2026-09-beta/flows/batch/read');
    $body = json_decode((string) $request->getBody(), true);
    expect($body['inputs'])->toBe($inputs);
    expect($result)->toBe(['results' => [['id' => 'flow-1'], ['id' => 'flow-2']]]);
});

it('actionTypes() sends GET /automation/2026-09-beta/action-types', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['results' => [['name' => 'send-email']]]),
    ]);

    $result = $client->automation()->flows()->actionTypes();

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('GET');
    expect($request->getUri()->getPath())->toBe('/automation/2026-09-beta/action-types');
    expect($result)->toBe(['results' => [['name' => 'send-email']]]);
});

it('version override changes the date segment in the path', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['results' => []]),
    ]);

    $client->automation()->flows('2026-03')->list();

    expect($mock->getLastRequest()->getUri()->getPath())->toBe('/automation/2026-03/flows');
});
