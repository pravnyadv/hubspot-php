<?php

declare(strict_types=1);

it('list() sends GET /automation/actions/2026-09/{appId}', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['results' => [['id' => 'def-1']]]),
    ]);

    $result = $client->automation()->actions()->list('12345');

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('GET');
    expect($request->getUri()->getPath())->toBe('/automation/actions/2026-09/12345');
    expect($result)->toBe(['results' => [['id' => 'def-1']]]);
});

it('list() passes query params', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['results' => []]),
    ]);

    $client->automation()->actions()->list('12345', ['limit' => 10, 'after' => 'cursor-abc']);

    parse_str($mock->getLastRequest()->getUri()->getQuery(), $query);
    expect($query['limit'])->toBe('10');
    expect($query['after'])->toBe('cursor-abc');
});

it('list() omits null query values', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['results' => []]),
    ]);

    $client->automation()->actions()->list('12345', ['limit' => null, 'after' => null]);

    parse_str($mock->getLastRequest()->getUri()->getQuery(), $query);
    expect($query)->not->toHaveKey('limit');
    expect($query)->not->toHaveKey('after');
});

it('all() paginates across two pages via the Paginator', function () {
    [$client] = mockClient([
        jsonResponse(200, [
            'results' => [['id' => 'def-1'], ['id' => 'def-2']],
            'paging' => ['next' => ['after' => 'page2cursor']],
        ]),
        jsonResponse(200, [
            'results' => [['id' => 'def-3']],
        ]),
    ]);

    $items = $client->automation()->actions()->all('12345')->all();

    expect($items)->toBe([['id' => 'def-1'], ['id' => 'def-2'], ['id' => 'def-3']]);
});

it('get() sends GET /automation/actions/2026-09/{appId}/{definitionId}', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['id' => 'def-1', 'actionUrl' => 'https://example.com/action']),
    ]);

    $result = $client->automation()->actions()->get('12345', 'def-1');

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('GET');
    expect($request->getUri()->getPath())->toBe('/automation/actions/2026-09/12345/def-1');
    expect($result)->toBe(['id' => 'def-1', 'actionUrl' => 'https://example.com/action']);
});

it('create() sends POST /automation/actions/2026-09/{appId} with body', function () {
    [$client, $mock] = mockClient([
        jsonResponse(201, ['id' => 'def-new']),
    ]);

    $result = $client->automation()->actions()->create('12345', ['actionUrl' => 'https://example.com/action', 'published' => true]);

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('POST');
    expect($request->getUri()->getPath())->toBe('/automation/actions/2026-09/12345');
    $body = json_decode((string) $request->getBody(), true);
    expect($body['actionUrl'])->toBe('https://example.com/action');
    expect($result)->toBe(['id' => 'def-new']);
});

it('update() sends PATCH /automation/actions/2026-09/{appId}/{definitionId}', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['id' => 'def-1', 'published' => false]),
    ]);

    $result = $client->automation()->actions()->update('12345', 'def-1', ['published' => false]);

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('PATCH');
    expect($request->getUri()->getPath())->toBe('/automation/actions/2026-09/12345/def-1');
    $body = json_decode((string) $request->getBody(), true);
    expect($body['published'])->toBeFalse();
    expect($result)->toBe(['id' => 'def-1', 'published' => false]);
});

it('archive() sends DELETE /automation/actions/2026-09/{appId}/{definitionId}', function () {
    [$client, $mock] = mockClient([
        jsonResponse(204),
    ]);

    $client->automation()->actions()->archive('12345', 'def-1');

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('DELETE');
    expect($request->getUri()->getPath())->toBe('/automation/actions/2026-09/12345/def-1');
});

it('functions() sends GET /automation/actions/2026-09/{appId}/{definitionId}/functions', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['results' => [['functionType' => 'PRE_ACTION_EXECUTION']]]),
    ]);

    $result = $client->automation()->actions()->functions('12345', 'def-1');

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('GET');
    expect($request->getUri()->getPath())->toBe('/automation/actions/2026-09/12345/def-1/functions');
    expect($result)->toBe(['results' => [['functionType' => 'PRE_ACTION_EXECUTION']]]);
});

it('getFunction() sends GET /automation/actions/2026-09/{appId}/{definitionId}/functions/{functionType}', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['functionType' => 'PRE_ACTION_EXECUTION', 'functionSource' => 'exports.main = () => ({});']),
    ]);

    $result = $client->automation()->actions()->getFunction('12345', 'def-1', 'PRE_ACTION_EXECUTION');

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('GET');
    expect($request->getUri()->getPath())->toBe('/automation/actions/2026-09/12345/def-1/functions/PRE_ACTION_EXECUTION');
    expect($result)->toBe(['functionType' => 'PRE_ACTION_EXECUTION', 'functionSource' => 'exports.main = () => ({});']);
});

it('putFunction() sends PUT /automation/actions/2026-09/{appId}/{definitionId}/functions/{functionType}', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['functionType' => 'PRE_ACTION_EXECUTION']),
    ]);

    $result = $client->automation()->actions()->putFunction('12345', 'def-1', 'PRE_ACTION_EXECUTION', ['functionSource' => 'exports.main = () => ({});']);

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('PUT');
    expect($request->getUri()->getPath())->toBe('/automation/actions/2026-09/12345/def-1/functions/PRE_ACTION_EXECUTION');
    $body = json_decode((string) $request->getBody(), true);
    expect($body['functionSource'])->toBe('exports.main = () => ({});');
});

it('deleteFunction() sends DELETE /automation/actions/2026-09/{appId}/{definitionId}/functions/{functionType}', function () {
    [$client, $mock] = mockClient([
        jsonResponse(204),
    ]);

    $client->automation()->actions()->deleteFunction('12345', 'def-1', 'PRE_ACTION_EXECUTION');

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('DELETE');
    expect($request->getUri()->getPath())->toBe('/automation/actions/2026-09/12345/def-1/functions/PRE_ACTION_EXECUTION');
});

it('revisions() sends GET /automation/actions/2026-09/{appId}/{definitionId}/revisions', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['results' => [['id' => 'rev-1']]]),
    ]);

    $result = $client->automation()->actions()->revisions('12345', 'def-1');

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('GET');
    expect($request->getUri()->getPath())->toBe('/automation/actions/2026-09/12345/def-1/revisions');
    expect($result)->toBe(['results' => [['id' => 'rev-1']]]);
});

it('revision() sends GET /automation/actions/2026-09/{appId}/{definitionId}/revisions/{revisionId}', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['id' => 'rev-1']),
    ]);

    $result = $client->automation()->actions()->revision('12345', 'def-1', 'rev-1');

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('GET');
    expect($request->getUri()->getPath())->toBe('/automation/actions/2026-09/12345/def-1/revisions/rev-1');
    expect($result)->toBe(['id' => 'rev-1']);
});

it('completeCallback() sends POST /automation/actions/callbacks/2026-09/{callbackId}/complete', function () {
    [$client, $mock] = mockClient([
        jsonResponse(204),
    ]);

    $client->automation()->actions()->completeCallback('cb-abc', ['outputFields' => ['result' => 'done']]);

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('POST');
    expect($request->getUri()->getPath())->toBe('/automation/actions/callbacks/2026-09/cb-abc/complete');
    $body = json_decode((string) $request->getBody(), true);
    expect($body['outputFields']['result'])->toBe('done');
});

it('completeCallbacks() sends POST /automation/actions/callbacks/2026-09/complete with inputs', function () {
    [$client, $mock] = mockClient([
        jsonResponse(204),
    ]);

    $inputs = [
        ['callbackId' => 'cb-1', 'outputFields' => ['result' => 'ok']],
        ['callbackId' => 'cb-2', 'outputFields' => ['result' => 'ok']],
    ];
    $client->automation()->actions()->completeCallbacks($inputs);

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('POST');
    expect($request->getUri()->getPath())->toBe('/automation/actions/callbacks/2026-09/complete');
    $body = json_decode((string) $request->getBody(), true);
    expect($body['inputs'])->toBe($inputs);
});

it('version override changes the date segment in the path', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['results' => []]),
    ]);

    $client->automation()->actions('2026-03')->list('12345');

    expect($mock->getLastRequest()->getUri()->getPath())->toBe('/automation/actions/2026-03/12345');
});
