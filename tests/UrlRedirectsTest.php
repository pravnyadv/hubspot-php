<?php

declare(strict_types=1);

it('list() sends GET /cms/url-redirects/2026-09', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['results' => [['id' => 'redirect-1']]]),
    ]);

    $result = $client->cms()->urlRedirects()->list();

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('GET');
    expect($request->getUri()->getPath())->toBe('/cms/url-redirects/2026-09');
    expect($result)->toBe(['results' => [['id' => 'redirect-1']]]);
});

it('list() passes query params and omits nulls', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['results' => []]),
    ]);

    $client->cms()->urlRedirects()->list(['limit' => 20, 'after' => null]);

    parse_str($mock->getLastRequest()->getUri()->getQuery(), $q);
    expect($q['limit'])->toBe('20');
    expect($q)->not->toHaveKey('after');
});

it('all() paginates across two pages', function () {
    [$client] = mockClient([
        jsonResponse(200, [
            'results' => [['id' => 'redirect-1']],
            'paging' => ['next' => ['after' => 'cursor2']],
        ]),
        jsonResponse(200, [
            'results' => [['id' => 'redirect-2']],
        ]),
    ]);

    $items = $client->cms()->urlRedirects()->all()->all();

    expect($items)->toBe([['id' => 'redirect-1'], ['id' => 'redirect-2']]);
});

it('get() sends GET /cms/url-redirects/2026-09/{urlRedirectId}', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['id' => 'redirect-1', 'routePrefix' => '/old', 'destination' => '/new']),
    ]);

    $result = $client->cms()->urlRedirects()->get('redirect-1');

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('GET');
    expect($request->getUri()->getPath())->toBe('/cms/url-redirects/2026-09/redirect-1');
    expect($result)->toBe(['id' => 'redirect-1', 'routePrefix' => '/old', 'destination' => '/new']);
});

it('create() sends POST /cms/url-redirects/2026-09', function () {
    [$client, $mock] = mockClient([
        jsonResponse(201, ['id' => 'redirect-new']),
    ]);

    $result = $client->cms()->urlRedirects()->create([
        'routePrefix' => '/old-page',
        'destination' => '/new-page',
        'redirectStyle' => 301,
    ]);

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('POST');
    expect($request->getUri()->getPath())->toBe('/cms/url-redirects/2026-09');
    $body = json_decode((string) $request->getBody(), true);
    expect($body['routePrefix'])->toBe('/old-page');
    expect($result)->toBe(['id' => 'redirect-new']);
});

it('update() sends PATCH /cms/url-redirects/2026-09/{urlRedirectId}', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['id' => 'redirect-1', 'destination' => '/updated']),
    ]);

    $result = $client->cms()->urlRedirects()->update('redirect-1', ['destination' => '/updated']);

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('PATCH');
    expect($request->getUri()->getPath())->toBe('/cms/url-redirects/2026-09/redirect-1');
    expect($result)->toBe(['id' => 'redirect-1', 'destination' => '/updated']);
});

it('archive() sends DELETE /cms/url-redirects/2026-09/{urlRedirectId}', function () {
    [$client, $mock] = mockClient([
        jsonResponse(204),
    ]);

    $client->cms()->urlRedirects()->archive('redirect-1');

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('DELETE');
    expect($request->getUri()->getPath())->toBe('/cms/url-redirects/2026-09/redirect-1');
});

it('version override changes the date segment in the path', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['results' => []]),
    ]);

    $client->cms()->urlRedirects('2026-03')->list();

    expect($mock->getLastRequest()->getUri()->getPath())->toBe('/cms/url-redirects/2026-03');
});
