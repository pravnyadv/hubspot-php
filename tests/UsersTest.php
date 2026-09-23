<?php

declare(strict_types=1);

it('list() sends GET /crm/objects/2026-09/users', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['results' => [['id' => 'user-1']]]),
    ]);

    $result = $client->crm()->users()->list();

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('GET');
    expect($request->getUri()->getPath())->toBe('/crm/objects/2026-09/users');
    expect($result)->toBe(['results' => [['id' => 'user-1']]]);
});

it('list() passes query params and omits nulls', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['results' => []]),
    ]);

    $client->crm()->users()->list(['limit' => 20, 'after' => null]);

    parse_str($mock->getLastRequest()->getUri()->getQuery(), $query);
    expect($query['limit'])->toBe('20');
    expect($query)->not->toHaveKey('after');
});

it('all() paginates across two pages', function () {
    [$client] = mockClient([
        jsonResponse(200, [
            'results' => [['id' => 'user-1']],
            'paging' => ['next' => ['after' => 'cursor2']],
        ]),
        jsonResponse(200, [
            'results' => [['id' => 'user-2']],
        ]),
    ]);

    $items = $client->crm()->users()->all()->all();

    expect($items)->toBe([['id' => 'user-1'], ['id' => 'user-2']]);
});

it('get() sends GET /crm/objects/2026-09/users/{userId}', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['id' => 'user-1', 'properties' => ['email' => 'user@example.com']]),
    ]);

    $result = $client->crm()->users()->get('user-1');

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('GET');
    expect($request->getUri()->getPath())->toBe('/crm/objects/2026-09/users/user-1');
    expect($result['id'])->toBe('user-1');
});

it('search() sends POST /crm/objects/2026-09/users/search', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['results' => [['id' => 'user-1']]]),
    ]);

    $result = $client->crm()->users()->search(['filterGroups' => [], 'limit' => 5]);

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('POST');
    expect($request->getUri()->getPath())->toBe('/crm/objects/2026-09/users/search');
    $body = json_decode((string) $request->getBody(), true);
    expect($body['limit'])->toBe(5);
    expect($result)->toBe(['results' => [['id' => 'user-1']]]);
});

it('batchCreate() sends POST /crm/objects/2026-09/users/batch/create with inputs wrapper', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['results' => [['id' => 'user-new']]]),
    ]);

    $client->crm()->users()->batchCreate([['properties' => ['email' => 'new@example.com']]]);

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('POST');
    expect($request->getUri()->getPath())->toBe('/crm/objects/2026-09/users/batch/create');
    $body = json_decode((string) $request->getBody(), true);
    expect($body)->toHaveKey('inputs');
});

it('batchRead() sends POST /crm/objects/2026-09/users/batch/read', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['results' => [['id' => 'user-1']]]),
    ]);

    $client->crm()->users()->batchRead([['id' => 'user-1']]);

    $request = $mock->getLastRequest();
    expect($request->getUri()->getPath())->toBe('/crm/objects/2026-09/users/batch/read');
    $body = json_decode((string) $request->getBody(), true);
    expect($body)->toHaveKey('inputs');
});

it('batchUpdate() sends POST /crm/objects/2026-09/users/batch/update', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['results' => []]),
    ]);

    $client->crm()->users()->batchUpdate([['id' => 'user-1', 'properties' => ['email' => 'upd@example.com']]]);

    expect($mock->getLastRequest()->getUri()->getPath())->toBe('/crm/objects/2026-09/users/batch/update');
});

it('batchUpsert() sends POST /crm/objects/2026-09/users/batch/upsert', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['results' => []]),
    ]);

    $client->crm()->users()->batchUpsert([['idProperty' => 'hs_user_id', 'id' => 'u1', 'properties' => []]]);

    expect($mock->getLastRequest()->getUri()->getPath())->toBe('/crm/objects/2026-09/users/batch/upsert');
});

it('batchArchive() sends POST /crm/objects/2026-09/users/batch/archive', function () {
    [$client, $mock] = mockClient([
        jsonResponse(204, []),
    ]);

    $client->crm()->users()->batchArchive([['id' => 'user-1']]);

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('POST');
    expect($request->getUri()->getPath())->toBe('/crm/objects/2026-09/users/batch/archive');
});

it('merge() sends POST /crm/objects/2026-09/users/merge', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['id' => 'user-merged']),
    ]);

    $result = $client->crm()->users()->merge(['primaryObjectId' => 'user-1', 'objectIdToMerge' => 'user-2']);

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('POST');
    expect($request->getUri()->getPath())->toBe('/crm/objects/2026-09/users/merge');
    expect($result)->toBe(['id' => 'user-merged']);
});

it('gdprDelete() sends POST /crm/objects/2026-09/users/gdpr-delete', function () {
    [$client, $mock] = mockClient([
        jsonResponse(204, []),
    ]);

    $client->crm()->users()->gdprDelete(['objectId' => 'user-1']);

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('POST');
    expect($request->getUri()->getPath())->toBe('/crm/objects/2026-09/users/gdpr-delete');
});

it('associations() sends GET /crm/objects/2026-09/users/{userId}/associations/{toObjectType}', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['results' => [['id' => 'contact-1']]]),
    ]);

    $result = $client->crm()->users()->associations('user-1', 'contacts');

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('GET');
    expect($request->getUri()->getPath())->toBe('/crm/objects/2026-09/users/user-1/associations/contacts');
    expect($result)->toBe(['results' => [['id' => 'contact-1']]]);
});

it('version override changes the date segment in the path', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['results' => []]),
    ]);

    $client->crm()->users('2026-03')->list();

    expect($mock->getLastRequest()->getUri()->getPath())->toBe('/crm/objects/2026-03/users');
});
