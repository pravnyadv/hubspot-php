<?php

declare(strict_types=1);

it('all() sends GET /crm/properties/2026-09/{objectType}', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['results' => [['name' => 'email']]]),
    ]);

    $result = $client->crm()->properties()->all('contacts');

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('GET');
    expect($request->getUri()->getPath())->toBe('/crm/properties/2026-09/contacts');
    expect($request->getUri()->getQuery())->toBe('');
    expect($result)->toBe(['results' => [['name' => 'email']]]);
});

it('get() sends GET /crm/properties/2026-09/{objectType}/{propertyName}', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['name' => 'firstname', 'type' => 'string']),
    ]);

    $result = $client->crm()->properties()->get('contacts', 'firstname');

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('GET');
    expect($request->getUri()->getPath())->toBe('/crm/properties/2026-09/contacts/firstname');
    expect($result)->toBe(['name' => 'firstname', 'type' => 'string']);
});

it('version override changes the date segment in the path', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['results' => []]),
    ]);

    $client->crm()->properties('2026-03')->all('deals');

    expect($mock->getLastRequest()->getUri()->getPath())->toBe('/crm/properties/2026-03/deals');
});

it('create() POSTs a property definition', function () {
    [$client, $mock] = mockClient([jsonResponse(201, ['name' => 'wa_consent'])]);

    $result = $client->crm()->properties()->create('contacts', ['name' => 'wa_consent', 'type' => 'bool']);

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('POST');
    expect($request->getUri()->getPath())->toBe('/crm/properties/2026-09/contacts');
    expect(json_decode((string) $request->getBody(), true))->toBe(['name' => 'wa_consent', 'type' => 'bool']);
    expect($result)->toBe(['name' => 'wa_consent']);
});

it('archive() DELETEs a property', function () {
    [$client, $mock] = mockClient([jsonResponse(204)]);

    $client->crm()->properties()->archive('contacts', 'wa_consent');

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('DELETE');
    expect($request->getUri()->getPath())->toBe('/crm/properties/2026-09/contacts/wa_consent');
});

it('property batch endpoints POST under the object type', function () {
    [$client, $mock] = mockClient([jsonResponse(200), jsonResponse(200), jsonResponse(204)]);

    $client->crm()->properties()->batchRead('contacts', [['name' => 'email']]);
    expect($mock->getLastRequest()->getUri()->getPath())->toBe('/crm/properties/2026-09/contacts/batch/read');
    $client->crm()->properties()->batchCreate('contacts', [['name' => 'x', 'type' => 'string']]);
    expect($mock->getLastRequest()->getUri()->getPath())->toBe('/crm/properties/2026-09/contacts/batch/create');
    $client->crm()->properties()->batchArchive('contacts', [['name' => 'x']]);
    expect($mock->getLastRequest()->getMethod())->toBe('POST');
    expect($mock->getLastRequest()->getUri()->getPath())->toBe('/crm/properties/2026-09/contacts/batch/archive');
});
