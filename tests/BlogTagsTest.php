<?php

declare(strict_types=1);

it('list() sends GET /cms/blogs/2026-09/tags', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['results' => [['id' => '1', 'name' => 'PHP']]]),
    ]);

    $result = $client->cms()->blogTags()->list();

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('GET');
    expect($request->getUri()->getPath())->toBe('/cms/blogs/2026-09/tags');
    expect($result)->toBe(['results' => [['id' => '1', 'name' => 'PHP']]]);
});

it('list() passes query params and drops nulls', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['results' => []]),
    ]);

    $client->cms()->blogTags()->list(['limit' => 50, 'after' => null]);

    parse_str($mock->getLastRequest()->getUri()->getQuery(), $query);
    expect($query['limit'])->toBe('50');
    expect($query)->not->toHaveKey('after');
});

it('all() paginates across two pages', function () {
    [$client] = mockClient([
        jsonResponse(200, [
            'results' => [['id' => '1'], ['id' => '2']],
            'paging' => ['next' => ['after' => 'cursor2']],
        ]),
        jsonResponse(200, [
            'results' => [['id' => '3']],
        ]),
    ]);

    $items = $client->cms()->blogTags()->all()->all();

    expect($items)->toBe([['id' => '1'], ['id' => '2'], ['id' => '3']]);
});

it('get() sends GET /cms/blogs/2026-09/tags/{id}', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['id' => '5', 'name' => 'Laravel']),
    ]);

    $result = $client->cms()->blogTags()->get('5');

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('GET');
    expect($request->getUri()->getPath())->toBe('/cms/blogs/2026-09/tags/5');
    expect($result)->toBe(['id' => '5', 'name' => 'Laravel']);
});

it('create() sends POST /cms/blogs/2026-09/tags with body', function () {
    [$client, $mock] = mockClient([
        jsonResponse(201, ['id' => '20', 'name' => 'Pest']),
    ]);

    $result = $client->cms()->blogTags()->create(['name' => 'Pest']);

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('POST');
    expect($request->getUri()->getPath())->toBe('/cms/blogs/2026-09/tags');
    expect(json_decode((string) $request->getBody(), true))->toBe(['name' => 'Pest']);
    expect($result)->toBe(['id' => '20', 'name' => 'Pest']);
});

it('update() sends PATCH /cms/blogs/2026-09/tags/{id}', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['id' => '5', 'name' => 'Laravel 12']),
    ]);

    $result = $client->cms()->blogTags()->update('5', ['name' => 'Laravel 12']);

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('PATCH');
    expect($request->getUri()->getPath())->toBe('/cms/blogs/2026-09/tags/5');
    expect(json_decode((string) $request->getBody(), true))->toBe(['name' => 'Laravel 12']);
    expect($result)->toBe(['id' => '5', 'name' => 'Laravel 12']);
});

it('archive() sends DELETE /cms/blogs/2026-09/tags/{id}', function () {
    [$client, $mock] = mockClient([
        jsonResponse(204),
    ]);

    $client->cms()->blogTags()->archive('5');

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('DELETE');
    expect($request->getUri()->getPath())->toBe('/cms/blogs/2026-09/tags/5');
});

it('batchCreate() sends POST /cms/blogs/2026-09/tags/batch/create', function () {
    [$client, $mock] = mockClient([
        jsonResponse(201, ['results' => [['id' => '21'], ['id' => '22']]]),
    ]);

    $result = $client->cms()->blogTags()->batchCreate([['name' => 'Tag A'], ['name' => 'Tag B']]);

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('POST');
    expect($request->getUri()->getPath())->toBe('/cms/blogs/2026-09/tags/batch/create');
    expect(json_decode((string) $request->getBody(), true))->toBe([
        'inputs' => [['name' => 'Tag A'], ['name' => 'Tag B']],
    ]);
    expect($result)->toBe(['results' => [['id' => '21'], ['id' => '22']]]);
});

it('batchRead() sends POST /cms/blogs/2026-09/tags/batch/read', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['results' => [['id' => '5', 'name' => 'Laravel']]]),
    ]);

    $result = $client->cms()->blogTags()->batchRead([['id' => '5']]);

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('POST');
    expect($request->getUri()->getPath())->toBe('/cms/blogs/2026-09/tags/batch/read');
    expect(json_decode((string) $request->getBody(), true))->toBe(['inputs' => [['id' => '5']]]);
    expect($result)->toBe(['results' => [['id' => '5', 'name' => 'Laravel']]]);
});

it('batchArchive() sends POST /cms/blogs/2026-09/tags/batch/archive', function () {
    [$client, $mock] = mockClient([
        jsonResponse(204),
    ]);

    $client->cms()->blogTags()->batchArchive([['id' => '1'], ['id' => '2']]);

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('POST');
    expect($request->getUri()->getPath())->toBe('/cms/blogs/2026-09/tags/batch/archive');
    expect(json_decode((string) $request->getBody(), true))->toBe([
        'inputs' => [['id' => '1'], ['id' => '2']],
    ]);
});

it('version override changes the date segment in the path', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['results' => []]),
    ]);

    $client->cms()->blogTags('2026-03')->list();

    expect($mock->getLastRequest()->getUri()->getPath())->toBe('/cms/blogs/2026-03/tags');
});
