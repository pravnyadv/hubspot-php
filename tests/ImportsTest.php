<?php

declare(strict_types=1);

it('list() sends GET /crm/imports/2026-09', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['results' => [['id' => 'import-1']]]),
    ]);

    $result = $client->crm()->imports()->list();

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('GET');
    expect($request->getUri()->getPath())->toBe('/crm/imports/2026-09');
    expect($result)->toBe(['results' => [['id' => 'import-1']]]);
});

it('list() passes query params and omits nulls', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['results' => []]),
    ]);

    $client->crm()->imports()->list(['limit' => 10, 'after' => null]);

    parse_str($mock->getLastRequest()->getUri()->getQuery(), $query);
    expect($query['limit'])->toBe('10');
    expect($query)->not->toHaveKey('after');
});

it('all() paginates across two pages', function () {
    [$client] = mockClient([
        jsonResponse(200, [
            'results' => [['id' => 'import-1']],
            'paging' => ['next' => ['after' => 'page2']],
        ]),
        jsonResponse(200, [
            'results' => [['id' => 'import-2']],
        ]),
    ]);

    $items = $client->crm()->imports()->all()->all();

    expect($items)->toBe([['id' => 'import-1'], ['id' => 'import-2']]);
});

it('get() sends GET /crm/imports/2026-09/{importId}', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['id' => 'import-1', 'state' => 'COMPLETE']),
    ]);

    $result = $client->crm()->imports()->get('import-1');

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('GET');
    expect($request->getUri()->getPath())->toBe('/crm/imports/2026-09/import-1');
    expect($result)->toBe(['id' => 'import-1', 'state' => 'COMPLETE']);
});

it('create() sends POST /crm/imports/2026-09 with multipart importRequest', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['id' => 'import-new', 'state' => 'STARTED']),
    ]);

    $result = $client->crm()->imports()->create(['name' => 'My Import']);

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('POST');
    expect($request->getUri()->getPath())->toBe('/crm/imports/2026-09');
    // multipart boundary is present in Content-Type
    expect($request->getHeaderLine('Content-Type'))->toContain('multipart/form-data');
    expect($result)->toBe(['id' => 'import-new', 'state' => 'STARTED']);
});

it('create() includes a files part when fileContent is provided', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['id' => 'import-csv']),
    ]);

    $client->crm()->imports()->create(['name' => 'CSV Import'], "col1,col2\nval1,val2", 'data.csv');

    $body = (string) $mock->getLastRequest()->getBody();
    expect($body)->toContain('data.csv');
    expect($body)->toContain('col1,col2');
});

it('cancel() sends POST /crm/imports/2026-09/{importId}/cancel', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, []),
    ]);

    $client->crm()->imports()->cancel('import-1');

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('POST');
    expect($request->getUri()->getPath())->toBe('/crm/imports/2026-09/import-1/cancel');
});

it('errors() sends GET /crm/imports/2026-09/{importId}/errors', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['results' => [['row' => 1, 'message' => 'bad value']]]),
    ]);

    $result = $client->crm()->imports()->errors('import-1');

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('GET');
    expect($request->getUri()->getPath())->toBe('/crm/imports/2026-09/import-1/errors');
    expect($result)->toBe(['results' => [['row' => 1, 'message' => 'bad value']]]);
});

it('version override changes the date segment in the path', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['results' => []]),
    ]);

    $client->crm()->imports('2026-03')->list();

    expect($mock->getLastRequest()->getUri()->getPath())->toBe('/crm/imports/2026-03');
});
