<?php

declare(strict_types=1);

// Tables - published

it('list() sends GET /cms/hubdb/2026-09/tables', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['results' => [['id' => 'my-table']]]),
    ]);

    $result = $client->cms()->hubdb()->list();

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('GET');
    expect($request->getUri()->getPath())->toBe('/cms/hubdb/2026-09/tables');
    expect($result)->toBe(['results' => [['id' => 'my-table']]]);
});

it('list() passes query params', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['results' => []]),
    ]);

    $client->cms()->hubdb()->list(['archived' => false]);

    parse_str($mock->getLastRequest()->getUri()->getQuery(), $q);
    expect($q['archived'])->toBe('false');
});

it('all() paginates across two pages', function () {
    [$client] = mockClient([
        jsonResponse(200, [
            'results' => [['id' => 'table-1']],
            'paging' => ['next' => ['after' => 'cursor2']],
        ]),
        jsonResponse(200, ['results' => [['id' => 'table-2']]]),
    ]);

    $items = $client->cms()->hubdb()->all()->all();

    expect($items)->toBe([['id' => 'table-1'], ['id' => 'table-2']]);
});

it('get() sends GET /cms/hubdb/2026-09/tables/{tableIdOrName}', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['id' => 'my-table', 'name' => 'My Table']),
    ]);

    $result = $client->cms()->hubdb()->get('my-table');

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('GET');
    expect($request->getUri()->getPath())->toBe('/cms/hubdb/2026-09/tables/my-table');
    expect($result)->toBe(['id' => 'my-table', 'name' => 'My Table']);
});

it('create() sends POST /cms/hubdb/2026-09/tables', function () {
    [$client, $mock] = mockClient([
        jsonResponse(201, ['id' => 'new-table']),
    ]);

    $result = $client->cms()->hubdb()->create(['name' => 'New Table', 'label' => 'New Table']);

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('POST');
    expect($request->getUri()->getPath())->toBe('/cms/hubdb/2026-09/tables');
    expect($result)->toBe(['id' => 'new-table']);
});

it('update() sends PATCH /cms/hubdb/2026-09/tables/{tableIdOrName}/draft', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['id' => 'my-table', 'label' => 'Updated']),
    ]);

    $result = $client->cms()->hubdb()->update('my-table', ['label' => 'Updated']);

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('PATCH');
    expect($request->getUri()->getPath())->toBe('/cms/hubdb/2026-09/tables/my-table/draft');
    expect($result)->toBe(['id' => 'my-table', 'label' => 'Updated']);
});

it('archive() sends DELETE /cms/hubdb/2026-09/tables/{tableIdOrName}', function () {
    [$client, $mock] = mockClient([
        jsonResponse(204),
    ]);

    $client->cms()->hubdb()->archive('my-table');

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('DELETE');
    expect($request->getUri()->getPath())->toBe('/cms/hubdb/2026-09/tables/my-table');
});

// Tables - draft

it('getDraft() sends GET /cms/hubdb/2026-09/tables/{id}/draft', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['id' => 'my-table', 'publishedAt' => null]),
    ]);

    $result = $client->cms()->hubdb()->getDraft('my-table');

    expect($mock->getLastRequest()->getUri()->getPath())->toBe('/cms/hubdb/2026-09/tables/my-table/draft');
    expect($result)->toBe(['id' => 'my-table', 'publishedAt' => null]);
});

it('publishDraft() sends POST /cms/hubdb/2026-09/tables/{id}/draft/publish', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['id' => 'my-table']),
    ]);

    $result = $client->cms()->hubdb()->publishDraft('my-table');

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('POST');
    expect($request->getUri()->getPath())->toBe('/cms/hubdb/2026-09/tables/my-table/draft/publish');
    expect($result)->toBe(['id' => 'my-table']);
});

it('resetDraft() sends POST /cms/hubdb/2026-09/tables/{id}/draft/reset', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['id' => 'my-table']),
    ]);

    $client->cms()->hubdb()->resetDraft('my-table');

    expect($mock->getLastRequest()->getUri()->getPath())->toBe('/cms/hubdb/2026-09/tables/my-table/draft/reset');
});

it('unpublish() sends POST /cms/hubdb/2026-09/tables/{id}/unpublish', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['id' => 'my-table']),
    ]);

    $client->cms()->hubdb()->unpublish('my-table');

    expect($mock->getLastRequest()->getUri()->getPath())->toBe('/cms/hubdb/2026-09/tables/my-table/unpublish');
});

// Rows - published

it('listRows() sends GET /cms/hubdb/2026-09/tables/{id}/rows', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['results' => [['id' => '1']]]),
    ]);

    $result = $client->cms()->hubdb()->listRows('my-table');

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('GET');
    expect($request->getUri()->getPath())->toBe('/cms/hubdb/2026-09/tables/my-table/rows');
    expect($result)->toBe(['results' => [['id' => '1']]]);
});

it('allRows() paginates', function () {
    [$client] = mockClient([
        jsonResponse(200, [
            'results' => [['id' => '1']],
            'paging' => ['next' => ['after' => 'c2']],
        ]),
        jsonResponse(200, ['results' => [['id' => '2']]]),
    ]);

    $items = $client->cms()->hubdb()->allRows('my-table')->all();

    expect($items)->toBe([['id' => '1'], ['id' => '2']]);
});

it('getRow() sends GET /cms/hubdb/2026-09/tables/{id}/rows/{rowId}', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['id' => '42', 'values' => []]),
    ]);

    $result = $client->cms()->hubdb()->getRow('my-table', '42');

    expect($mock->getLastRequest()->getUri()->getPath())->toBe('/cms/hubdb/2026-09/tables/my-table/rows/42');
    expect($result)->toBe(['id' => '42', 'values' => []]);
});

it('createRow() sends POST /cms/hubdb/2026-09/tables/{id}/rows', function () {
    [$client, $mock] = mockClient([
        jsonResponse(201, ['id' => '99']),
    ]);

    $result = $client->cms()->hubdb()->createRow('my-table', ['values' => ['col' => 'val']]);

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('POST');
    expect($request->getUri()->getPath())->toBe('/cms/hubdb/2026-09/tables/my-table/rows');
    expect($result)->toBe(['id' => '99']);
});

it('updateRow() sends PATCH /cms/hubdb/2026-09/tables/{id}/rows/{rowId}/draft', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['id' => '42']),
    ]);

    $client->cms()->hubdb()->updateRow('my-table', '42', ['values' => ['col' => 'new']]);

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('PATCH');
    expect($request->getUri()->getPath())->toBe('/cms/hubdb/2026-09/tables/my-table/rows/42/draft');
});

it('archiveRow() sends DELETE /cms/hubdb/2026-09/tables/{id}/rows/{rowId}/draft', function () {
    [$client, $mock] = mockClient([
        jsonResponse(204),
    ]);

    $client->cms()->hubdb()->archiveRow('my-table', '42');

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('DELETE');
    expect($request->getUri()->getPath())->toBe('/cms/hubdb/2026-09/tables/my-table/rows/42/draft');
});

// Rows - draft

it('listDraftRows() sends GET /cms/hubdb/2026-09/tables/{id}/rows/draft', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['results' => []]),
    ]);

    $client->cms()->hubdb()->listDraftRows('my-table');

    expect($mock->getLastRequest()->getUri()->getPath())->toBe('/cms/hubdb/2026-09/tables/my-table/rows/draft');
});

it('updateDraftRow() sends PATCH /cms/hubdb/2026-09/tables/{id}/rows/{rowId}/draft', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['id' => '55']),
    ]);

    $client->cms()->hubdb()->updateDraftRow('my-table', '55', ['values' => []]);

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('PATCH');
    expect($request->getUri()->getPath())->toBe('/cms/hubdb/2026-09/tables/my-table/rows/55/draft');
});

// Batch

it('batchReadRows() sends POST /cms/hubdb/2026-09/tables/{id}/rows/batch/read', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['results' => [['id' => '1']]]),
    ]);

    $result = $client->cms()->hubdb()->batchReadRows('my-table', [['id' => '1']]);

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('POST');
    expect($request->getUri()->getPath())->toBe('/cms/hubdb/2026-09/tables/my-table/rows/batch/read');
    $body = json_decode((string) $request->getBody(), true);
    expect($body['inputs'])->toBe([['id' => '1']]);
    expect($result)->toBe(['results' => [['id' => '1']]]);
});

it('batchCreateDraftRows() sends POST .../rows/draft/batch/create', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['results' => []]),
    ]);

    $client->cms()->hubdb()->batchCreateDraftRows('my-table', [['values' => []]]);

    expect($mock->getLastRequest()->getUri()->getPath())->toBe('/cms/hubdb/2026-09/tables/my-table/rows/draft/batch/create');
});

it('batchUpdateDraftRows() sends POST .../rows/draft/batch/update', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['results' => []]),
    ]);

    $client->cms()->hubdb()->batchUpdateDraftRows('my-table', [['id' => '1', 'values' => []]]);

    expect($mock->getLastRequest()->getUri()->getPath())->toBe('/cms/hubdb/2026-09/tables/my-table/rows/draft/batch/update');
});

// Version override

it('version override changes the date segment in the path', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['results' => []]),
    ]);

    $client->cms()->hubdb('2026-03')->list();

    expect($mock->getLastRequest()->getUri()->getPath())->toBe('/cms/hubdb/2026-03/tables');
});

it('version override applies to row paths too', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['results' => []]),
    ]);

    $client->cms()->hubdb('2026-03')->listRows('tbl');

    expect($mock->getLastRequest()->getUri()->getPath())->toBe('/cms/hubdb/2026-03/tables/tbl/rows');
});
