<?php

declare(strict_types=1);

it('list() sends GET /cms/blogs/2026-09/posts', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['results' => [['id' => '1', 'name' => 'My Post']]]),
    ]);

    $result = $client->cms()->blogPosts()->list();

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('GET');
    expect($request->getUri()->getPath())->toBe('/cms/blogs/2026-09/posts');
    expect($result)->toBe(['results' => [['id' => '1', 'name' => 'My Post']]]);
});

it('list() forwards query params and drops nulls', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['results' => []]),
    ]);

    $client->cms()->blogPosts()->list(['limit' => 20, 'after' => null, 'state' => 'PUBLISHED']);

    parse_str($mock->getLastRequest()->getUri()->getQuery(), $query);
    expect($query['limit'])->toBe('20');
    expect($query['state'])->toBe('PUBLISHED');
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

    $items = $client->cms()->blogPosts()->all()->all();

    expect($items)->toBe([['id' => '1'], ['id' => '2'], ['id' => '3']]);
});

it('get() sends GET /cms/blogs/2026-09/posts/{id}', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['id' => '42', 'name' => 'Hello World']),
    ]);

    $result = $client->cms()->blogPosts()->get('42');

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('GET');
    expect($request->getUri()->getPath())->toBe('/cms/blogs/2026-09/posts/42');
    expect($result)->toBe(['id' => '42', 'name' => 'Hello World']);
});

it('create() sends POST /cms/blogs/2026-09/posts with body', function () {
    [$client, $mock] = mockClient([
        jsonResponse(201, ['id' => '99', 'name' => 'New Post']),
    ]);

    $result = $client->cms()->blogPosts()->create(['name' => 'New Post', 'contentGroupId' => 'blog-1']);

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('POST');
    expect($request->getUri()->getPath())->toBe('/cms/blogs/2026-09/posts');
    expect(json_decode((string) $request->getBody(), true))->toBe(['name' => 'New Post', 'contentGroupId' => 'blog-1']);
    expect($result)->toBe(['id' => '99', 'name' => 'New Post']);
});

it('update() sends PATCH /cms/blogs/2026-09/posts/{id}', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['id' => '42', 'name' => 'Updated Post']),
    ]);

    $result = $client->cms()->blogPosts()->update('42', ['name' => 'Updated Post']);

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('PATCH');
    expect($request->getUri()->getPath())->toBe('/cms/blogs/2026-09/posts/42');
    expect(json_decode((string) $request->getBody(), true))->toBe(['name' => 'Updated Post']);
    expect($result)->toBe(['id' => '42', 'name' => 'Updated Post']);
});

it('archive() sends DELETE /cms/blogs/2026-09/posts/{id}', function () {
    [$client, $mock] = mockClient([
        jsonResponse(204),
    ]);

    $client->cms()->blogPosts()->archive('42');

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('DELETE');
    expect($request->getUri()->getPath())->toBe('/cms/blogs/2026-09/posts/42');
});

it('batchCreate() sends POST /cms/blogs/2026-09/posts/batch/create', function () {
    [$client, $mock] = mockClient([
        jsonResponse(201, ['results' => [['id' => '1'], ['id' => '2']]]),
    ]);

    $result = $client->cms()->blogPosts()->batchCreate([['name' => 'Post A'], ['name' => 'Post B']]);

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('POST');
    expect($request->getUri()->getPath())->toBe('/cms/blogs/2026-09/posts/batch/create');
    expect(json_decode((string) $request->getBody(), true))->toBe([
        'inputs' => [['name' => 'Post A'], ['name' => 'Post B']],
    ]);
    expect($result)->toBe(['results' => [['id' => '1'], ['id' => '2']]]);
});

it('duplicate() sends POST /cms/blogs/2026-09/posts/clone', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['id' => '100', 'name' => 'Copy of post']),
    ]);

    $result = $client->cms()->blogPosts()->duplicate(['id' => '42', 'name' => 'Copy of post']);

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('POST');
    expect($request->getUri()->getPath())->toBe('/cms/blogs/2026-09/posts/clone');
    expect($result)->toBe(['id' => '100', 'name' => 'Copy of post']);
});

it('getDraft() sends GET /cms/blogs/2026-09/posts/{id}/draft', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['id' => '42', 'isDraft' => true]),
    ]);

    $result = $client->cms()->blogPosts()->getDraft('42');

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('GET');
    expect($request->getUri()->getPath())->toBe('/cms/blogs/2026-09/posts/42/draft');
    expect($result)->toBe(['id' => '42', 'isDraft' => true]);
});

it('pushLive() sends POST /cms/blogs/2026-09/posts/{id}/draft/push-live', function () {
    [$client, $mock] = mockClient([
        jsonResponse(204),
    ]);

    $client->cms()->blogPosts()->pushLive('42');

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('POST');
    expect($request->getUri()->getPath())->toBe('/cms/blogs/2026-09/posts/42/draft/push-live');
});

it('revisions() sends GET /cms/blogs/2026-09/posts/{id}/revisions', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['results' => [['id' => 'rev-1']]]),
    ]);

    $result = $client->cms()->blogPosts()->revisions('42');

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('GET');
    expect($request->getUri()->getPath())->toBe('/cms/blogs/2026-09/posts/42/revisions');
    expect($result)->toBe(['results' => [['id' => 'rev-1']]]);
});

it('restoreRevision() sends POST /cms/blogs/2026-09/posts/{id}/revisions/{revisionId}/restore', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['id' => '42']),
    ]);

    $result = $client->cms()->blogPosts()->restoreRevision('42', 'rev-1');

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('POST');
    expect($request->getUri()->getPath())->toBe('/cms/blogs/2026-09/posts/42/revisions/rev-1/restore');
    expect($result)->toBe(['id' => '42']);
});

it('version override changes the date segment in the path', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['results' => []]),
    ]);

    $client->cms()->blogPosts('2026-03')->list();

    expect($mock->getLastRequest()->getUri()->getPath())->toBe('/cms/blogs/2026-03/posts');
});
