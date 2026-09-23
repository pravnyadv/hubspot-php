<?php

declare(strict_types=1);

it('list() sends GET /crm/object-tags/2026-09/{objectTypeId}', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['results' => [['id' => 'tag-1', 'name' => 'Hot']]]),
    ]);

    $result = $client->crm()->objectTags()->list('0-1');

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('GET');
    expect($request->getUri()->getPath())->toBe('/crm/object-tags/2026-09/0-1');
    expect($result)->toBe(['results' => [['id' => 'tag-1', 'name' => 'Hot']]]);
});

it('list() passes query params and omits nulls', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['results' => []]),
    ]);

    $client->crm()->objectTags()->list('0-1', ['limit' => 5, 'after' => null]);

    parse_str($mock->getLastRequest()->getUri()->getQuery(), $query);
    expect($query['limit'])->toBe('5');
    expect($query)->not->toHaveKey('after');
});

it('get() sends GET /crm/object-tags/2026-09/{objectTypeId}/{tagId}', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['id' => 'tag-1', 'name' => 'Hot']),
    ]);

    $result = $client->crm()->objectTags()->get('0-1', 'tag-1');

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('GET');
    expect($request->getUri()->getPath())->toBe('/crm/object-tags/2026-09/0-1/tag-1');
    expect($result)->toBe(['id' => 'tag-1', 'name' => 'Hot']);
});

it('create() sends POST /crm/object-tags/2026-09/{objectTypeId} with body', function () {
    [$client, $mock] = mockClient([
        jsonResponse(201, ['id' => 'tag-new', 'name' => 'Cold']),
    ]);

    $result = $client->crm()->objectTags()->create('0-1', ['name' => 'Cold', 'color' => 'BLUE']);

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('POST');
    expect($request->getUri()->getPath())->toBe('/crm/object-tags/2026-09/0-1');
    $body = json_decode((string) $request->getBody(), true);
    expect($body['name'])->toBe('Cold');
    expect($result['id'])->toBe('tag-new');
});

it('update() sends PATCH /crm/object-tags/2026-09/{objectTypeId}/{tagId}', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['id' => 'tag-1', 'name' => 'Warm']),
    ]);

    $result = $client->crm()->objectTags()->update('0-1', 'tag-1', ['name' => 'Warm']);

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('PATCH');
    expect($request->getUri()->getPath())->toBe('/crm/object-tags/2026-09/0-1/tag-1');
    $body = json_decode((string) $request->getBody(), true);
    expect($body['name'])->toBe('Warm');
    expect($result)->toBe(['id' => 'tag-1', 'name' => 'Warm']);
});

it('delete() sends DELETE /crm/object-tags/2026-09/{objectTypeId}/{tagId}', function () {
    [$client, $mock] = mockClient([
        jsonResponse(204, []),
    ]);

    $client->crm()->objectTags()->delete('0-1', 'tag-1');

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('DELETE');
    expect($request->getUri()->getPath())->toBe('/crm/object-tags/2026-09/0-1/tag-1');
});

it('version override changes the date segment in the path', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['results' => []]),
    ]);

    $client->crm()->objectTags('2026-03')->list('0-1');

    expect($mock->getLastRequest()->getUri()->getPath())->toBe('/crm/object-tags/2026-03/0-1');
});
