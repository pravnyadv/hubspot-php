<?php

declare(strict_types=1);

it('list() sends GET /cms/blogs/2026-09/authors', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['results' => [['id' => '1', 'displayName' => 'Alice']]]),
    ]);

    $result = $client->cms()->blogAuthors()->list();

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('GET');
    expect($request->getUri()->getPath())->toBe('/cms/blogs/2026-09/authors');
    expect($result)->toBe(['results' => [['id' => '1', 'displayName' => 'Alice']]]);
});

it('list() passes query params and drops nulls', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['results' => []]),
    ]);

    $client->cms()->blogAuthors()->list(['limit' => 10, 'after' => null]);

    parse_str($mock->getLastRequest()->getUri()->getQuery(), $query);
    expect($query['limit'])->toBe('10');
    expect($query)->not->toHaveKey('after');
});

it('all() paginates across two pages', function () {
    [$client] = mockClient([
        jsonResponse(200, [
            'results' => [['id' => '1'], ['id' => '2']],
            'paging' => ['next' => ['after' => 'page2']],
        ]),
        jsonResponse(200, [
            'results' => [['id' => '3']],
        ]),
    ]);

    $items = $client->cms()->blogAuthors()->all()->all();

    expect($items)->toBe([['id' => '1'], ['id' => '2'], ['id' => '3']]);
});

it('get() sends GET /cms/blogs/2026-09/authors/{id}', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['id' => '7', 'displayName' => 'Bob']),
    ]);

    $result = $client->cms()->blogAuthors()->get('7');

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('GET');
    expect($request->getUri()->getPath())->toBe('/cms/blogs/2026-09/authors/7');
    expect($result)->toBe(['id' => '7', 'displayName' => 'Bob']);
});

it('create() sends POST /cms/blogs/2026-09/authors with body', function () {
    [$client, $mock] = mockClient([
        jsonResponse(201, ['id' => '10', 'displayName' => 'Carol']),
    ]);

    $result = $client->cms()->blogAuthors()->create(['displayName' => 'Carol', 'email' => 'carol@example.com']);

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('POST');
    expect($request->getUri()->getPath())->toBe('/cms/blogs/2026-09/authors');
    expect(json_decode((string) $request->getBody(), true))->toBe(['displayName' => 'Carol', 'email' => 'carol@example.com']);
    expect($result)->toBe(['id' => '10', 'displayName' => 'Carol']);
});

it('update() sends PATCH /cms/blogs/2026-09/authors/{id}', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['id' => '7', 'displayName' => 'Bobby']),
    ]);

    $result = $client->cms()->blogAuthors()->update('7', ['displayName' => 'Bobby']);

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('PATCH');
    expect($request->getUri()->getPath())->toBe('/cms/blogs/2026-09/authors/7');
    expect(json_decode((string) $request->getBody(), true))->toBe(['displayName' => 'Bobby']);
    expect($result)->toBe(['id' => '7', 'displayName' => 'Bobby']);
});

it('archive() sends DELETE /cms/blogs/2026-09/authors/{id}', function () {
    [$client, $mock] = mockClient([
        jsonResponse(204),
    ]);

    $client->cms()->blogAuthors()->archive('7');

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('DELETE');
    expect($request->getUri()->getPath())->toBe('/cms/blogs/2026-09/authors/7');
});

it('batchCreate() sends POST /cms/blogs/2026-09/authors/batch/create', function () {
    [$client, $mock] = mockClient([
        jsonResponse(201, ['results' => [['id' => '11'], ['id' => '12']]]),
    ]);

    $result = $client->cms()->blogAuthors()->batchCreate([
        ['displayName' => 'Dave'],
        ['displayName' => 'Eve'],
    ]);

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('POST');
    expect($request->getUri()->getPath())->toBe('/cms/blogs/2026-09/authors/batch/create');
    expect(json_decode((string) $request->getBody(), true))->toBe([
        'inputs' => [['displayName' => 'Dave'], ['displayName' => 'Eve']],
    ]);
    expect($result)->toBe(['results' => [['id' => '11'], ['id' => '12']]]);
});

it('batchArchive() sends POST /cms/blogs/2026-09/authors/batch/archive', function () {
    [$client, $mock] = mockClient([
        jsonResponse(204),
    ]);

    $client->cms()->blogAuthors()->batchArchive([['id' => '1'], ['id' => '2']]);

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('POST');
    expect($request->getUri()->getPath())->toBe('/cms/blogs/2026-09/authors/batch/archive');
    expect(json_decode((string) $request->getBody(), true))->toBe([
        'inputs' => [['id' => '1'], ['id' => '2']],
    ]);
});

it('version override changes the date segment in the path', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['results' => []]),
    ]);

    $client->cms()->blogAuthors('2026-03')->list();

    expect($mock->getLastRequest()->getUri()->getPath())->toBe('/cms/blogs/2026-03/authors');
});
