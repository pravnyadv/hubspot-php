<?php

declare(strict_types=1);

it('list() sends GET /cms/blog-settings/2026-09/settings', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['results' => [['id' => 'blog-1', 'name' => 'Main Blog']]]),
    ]);

    $result = $client->cms()->blogSettings()->list();

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('GET');
    expect($request->getUri()->getPath())->toBe('/cms/blog-settings/2026-09/settings');
    expect($result)->toBe(['results' => [['id' => 'blog-1', 'name' => 'Main Blog']]]);
});

it('list() passes query params and drops nulls', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['results' => []]),
    ]);

    $client->cms()->blogSettings()->list(['limit' => 5, 'after' => null]);

    parse_str($mock->getLastRequest()->getUri()->getQuery(), $query);
    expect($query['limit'])->toBe('5');
    expect($query)->not->toHaveKey('after');
});

it('all() paginates across two pages', function () {
    [$client] = mockClient([
        jsonResponse(200, [
            'results' => [['id' => 'blog-1']],
            'paging' => ['next' => ['after' => 'page2']],
        ]),
        jsonResponse(200, [
            'results' => [['id' => 'blog-2']],
        ]),
    ]);

    $items = $client->cms()->blogSettings()->all()->all();

    expect($items)->toBe([['id' => 'blog-1'], ['id' => 'blog-2']]);
});

it('get() sends GET /cms/blog-settings/2026-09/settings/{blogId}', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['id' => 'blog-1', 'name' => 'Main Blog']),
    ]);

    $result = $client->cms()->blogSettings()->get('blog-1');

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('GET');
    expect($request->getUri()->getPath())->toBe('/cms/blog-settings/2026-09/settings/blog-1');
    expect($result)->toBe(['id' => 'blog-1', 'name' => 'Main Blog']);
});

it('revisions() sends GET /cms/blog-settings/2026-09/settings/{blogId}/revisions', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['results' => [['id' => 'rev-1']]]),
    ]);

    $result = $client->cms()->blogSettings()->revisions('blog-1');

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('GET');
    expect($request->getUri()->getPath())->toBe('/cms/blog-settings/2026-09/settings/blog-1/revisions');
    expect($result)->toBe(['results' => [['id' => 'rev-1']]]);
});

it('revision() sends GET /cms/blog-settings/2026-09/settings/{blogId}/revisions/{revisionId}', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['id' => 'rev-1', 'createdAt' => '2026-01-01T00:00:00Z']),
    ]);

    $result = $client->cms()->blogSettings()->revision('blog-1', 'rev-1');

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('GET');
    expect($request->getUri()->getPath())->toBe('/cms/blog-settings/2026-09/settings/blog-1/revisions/rev-1');
    expect($result)->toBe(['id' => 'rev-1', 'createdAt' => '2026-01-01T00:00:00Z']);
});

it('version override changes the date segment in the path', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['results' => []]),
    ]);

    $client->cms()->blogSettings('2026-03')->list();

    expect($mock->getLastRequest()->getUri()->getPath())->toBe('/cms/blog-settings/2026-03/settings');
});
