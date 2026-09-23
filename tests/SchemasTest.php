<?php

declare(strict_types=1);

it('all() sends GET /crm-object-schemas/2026-09/schemas', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['results' => [['name' => 'my_object']]]),
    ]);

    $result = $client->crm()->schemas()->all();

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('GET');
    expect($request->getUri()->getPath())->toBe('/crm-object-schemas/2026-09/schemas');
    expect($result)->toBe(['results' => [['name' => 'my_object']]]);
});

it('get() sends GET /crm-object-schemas/2026-09/schemas/{objectType}', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['name' => 'my_object', 'objectTypeId' => '2-12345']),
    ]);

    $result = $client->crm()->schemas()->get('my_object');

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('GET');
    expect($request->getUri()->getPath())->toBe('/crm-object-schemas/2026-09/schemas/my_object');
    expect($result)->toBe(['name' => 'my_object', 'objectTypeId' => '2-12345']);
});

it('create() sends POST /crm-object-schemas/2026-09/schemas with json body passthrough', function () {
    $schema = [
        'name' => 'my_object',
        'labels' => ['singular' => 'My Object', 'plural' => 'My Objects'],
        'primaryDisplayProperty' => 'my_object_property',
        'properties' => [
            ['name' => 'my_object_property', 'label' => 'My Object Property', 'type' => 'string', 'fieldType' => 'text'],
        ],
    ];

    [$client, $mock] = mockClient([
        jsonResponse(200, ['name' => 'my_object', 'objectTypeId' => '2-12345']),
    ]);

    $result = $client->crm()->schemas()->create($schema);

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('POST');
    expect($request->getUri()->getPath())->toBe('/crm-object-schemas/2026-09/schemas');
    $body = json_decode((string) $request->getBody(), true);
    expect($body)->toBe($schema);
    expect($result)->toBe(['name' => 'my_object', 'objectTypeId' => '2-12345']);
});

it('archive() sends DELETE /crm-object-schemas/2026-09/schemas/{objectType}', function () {
    [$client, $mock] = mockClient([
        jsonResponse(204, []),
    ]);

    $result = $client->crm()->schemas()->archive('my_object');

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('DELETE');
    expect($request->getUri()->getPath())->toBe('/crm-object-schemas/2026-09/schemas/my_object');
    expect($result)->toBeNull();
});

it('version override changes the date segment in the path', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['results' => []]),
    ]);

    $client->crm()->schemas('2026-03')->all();

    expect($mock->getLastRequest()->getUri()->getPath())->toBe('/crm-object-schemas/2026-03/schemas');
});
