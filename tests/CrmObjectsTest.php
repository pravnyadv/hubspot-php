<?php

declare(strict_types=1);

test('get fetches a contact by id with default version in path', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['id' => '123', 'properties' => ['firstname' => 'John']]),
    ]);

    $result = $client->crm()->objects('contacts')->get('123');

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('GET');
    expect($request->getUri()->getPath())->toBe('/crm/objects/2026-09/contacts/123');
    expect($result)->toBe(['id' => '123', 'properties' => ['firstname' => 'John']]);
});

test('get joins properties into a comma-separated query param', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['id' => '123', 'properties' => ['firstname' => 'John', 'lastname' => 'Doe']]),
    ]);

    $client->crm()->objects('contacts')->get('123', ['firstname', 'lastname']);

    $request = $mock->getLastRequest();
    parse_str($request->getUri()->getQuery(), $query);
    expect($query['properties'])->toBe('firstname,lastname');
    expect($query)->not->toHaveKey('propertiesWithHistory');
});

test('get joins propertiesWithHistory into a comma-separated query param', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['id' => '123']),
    ]);

    $client->crm()->objects('contacts')->get('123', [], ['createdate', 'hs_lastmodifieddate']);

    $request = $mock->getLastRequest();
    parse_str($request->getUri()->getQuery(), $query);
    expect($query['propertiesWithHistory'])->toBe('createdate,hs_lastmodifieddate');
    expect($query)->not->toHaveKey('properties');
});

test('get omits empty property arrays from query', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['id' => '123']),
    ]);

    $client->crm()->objects('contacts')->get('123');

    $request = $mock->getLastRequest();
    parse_str($request->getUri()->getQuery(), $query);
    expect($query)->not->toHaveKey('properties');
    expect($query)->not->toHaveKey('propertiesWithHistory');
});

test('create posts properties json body', function () {
    [$client, $mock] = mockClient([
        jsonResponse(201, ['id' => '456', 'properties' => ['email' => 'test@example.com']]),
    ]);

    $result = $client->crm()->objects('contacts')->create(['email' => 'test@example.com']);

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('POST');
    expect($request->getUri()->getPath())->toBe('/crm/objects/2026-09/contacts');
    expect(json_decode((string) $request->getBody(), true))->toBe([
        'properties' => ['email' => 'test@example.com'],
    ]);
    expect($result)->toBe(['id' => '456', 'properties' => ['email' => 'test@example.com']]);
});

test('update patches properties json body to the correct path', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['id' => '123', 'properties' => ['firstname' => 'Jane']]),
    ]);

    $result = $client->crm()->objects('contacts')->update('123', ['firstname' => 'Jane']);

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('PATCH');
    expect($request->getUri()->getPath())->toBe('/crm/objects/2026-09/contacts/123');
    expect(json_decode((string) $request->getBody(), true))->toBe([
        'properties' => ['firstname' => 'Jane'],
    ]);
    expect($result)->toBe(['id' => '123', 'properties' => ['firstname' => 'Jane']]);
});

test('archive sends a DELETE to the correct path', function () {
    [$client, $mock] = mockClient([
        jsonResponse(204),
    ]);

    $client->crm()->objects('contacts')->archive('123');

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('DELETE');
    expect($request->getUri()->getPath())->toBe('/crm/objects/2026-09/contacts/123');
});

test('search posts the request body verbatim', function () {
    $searchRequest = [
        'filterGroups' => [
            ['filters' => [['propertyName' => 'email', 'operator' => 'EQ', 'value' => 'test@example.com']]],
        ],
        'properties' => ['email', 'firstname'],
        'limit' => 10,
    ];

    [$client, $mock] = mockClient([
        jsonResponse(200, ['total' => 1, 'results' => [['id' => '123']]]),
    ]);

    $result = $client->crm()->objects('contacts')->search($searchRequest);

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('POST');
    expect($request->getUri()->getPath())->toBe('/crm/objects/2026-09/contacts/search');
    expect(json_decode((string) $request->getBody(), true))->toBe($searchRequest);
    expect($result)->toBe(['total' => 1, 'results' => [['id' => '123']]]);
});

test('works with a non-standard object type in the path', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['id' => '789']),
    ]);

    $client->crm()->objects('deals')->get('789');

    $request = $mock->getLastRequest();
    expect($request->getUri()->getPath())->toBe('/crm/objects/2026-09/deals/789');
});

test('per-resource version override changes the date segment in the path', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['id' => '123']),
    ]);

    $client->crm()->objects('contacts', '2026-03')->get('123');

    $request = $mock->getLastRequest();
    expect($request->getUri()->getPath())->toBe('/crm/objects/2026-03/contacts/123');
});

test('path does not percent-encode the object type or version', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['id' => '1']),
    ]);

    $client->crm()->objects('contacts')->get('1');

    $path = $mock->getLastRequest()->getUri()->getPath();
    expect($path)->toBe('/crm/objects/2026-09/contacts/1');
    expect($path)->not->toContain('%');
});

it('batchRead() POSTs /batch/read with an inputs body', function () {
    [$client, $mock] = mockClient([jsonResponse(200, ['results' => [['id' => '1']]])]);

    $client->crm()->contacts()->batchRead([['id' => '1']]);

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('POST');
    expect($request->getUri()->getPath())->toBe('/crm/objects/2026-09/contacts/batch/read');
    expect(json_decode((string) $request->getBody(), true))->toBe(['inputs' => [['id' => '1']]]);
});

it('batch create/update/upsert/archive hit the right POST paths', function () {
    [$client, $mock] = mockClient([jsonResponse(201), jsonResponse(200), jsonResponse(200), jsonResponse(204)]);

    $client->crm()->contacts()->batchCreate([['properties' => []]]);
    expect($mock->getLastRequest()->getUri()->getPath())->toBe('/crm/objects/2026-09/contacts/batch/create');
    $client->crm()->contacts()->batchUpdate([['id' => '1']]);
    expect($mock->getLastRequest()->getUri()->getPath())->toBe('/crm/objects/2026-09/contacts/batch/update');
    $client->crm()->contacts()->batchUpsert([['id' => '1']]);
    expect($mock->getLastRequest()->getUri()->getPath())->toBe('/crm/objects/2026-09/contacts/batch/upsert');
    $client->crm()->contacts()->batchArchive([['id' => '1']]);
    expect($mock->getLastRequest()->getMethod())->toBe('POST');
    expect($mock->getLastRequest()->getUri()->getPath())->toBe('/crm/objects/2026-09/contacts/batch/archive');
});

it('merge() and gdprDelete() POST the right paths', function () {
    [$client, $mock] = mockClient([jsonResponse(200), jsonResponse(204)]);

    $client->crm()->contacts()->merge(['primaryObjectId' => '1', 'objectIdToMerge' => '2']);
    expect($mock->getLastRequest()->getUri()->getPath())->toBe('/crm/objects/2026-09/contacts/merge');
    $client->crm()->contacts()->gdprDelete(['objectId' => '1']);
    expect($mock->getLastRequest()->getUri()->getPath())->toBe('/crm/objects/2026-09/contacts/gdpr-delete');
});
