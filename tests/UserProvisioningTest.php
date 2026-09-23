<?php

declare(strict_types=1);

it('list() sends GET /settings/users/2026-09', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['results' => [['id' => 'u1', 'email' => 'alice@example.com']]]),
    ]);

    $result = $client->settings()->users()->list();

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('GET');
    expect($request->getUri()->getPath())->toBe('/settings/users/2026-09');
    expect($result)->toBe(['results' => [['id' => 'u1', 'email' => 'alice@example.com']]]);
});

it('list() forwards query params and omits nulls', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['results' => []]),
    ]);

    $client->settings()->users()->list(['after' => 'cursor-1', 'limit' => null]);

    parse_str($mock->getLastRequest()->getUri()->getQuery(), $query);
    expect($query['after'])->toBe('cursor-1');
    expect($query)->not->toHaveKey('limit');
});

it('all() paginates across two pages via Paginator', function () {
    [$client] = mockClient([
        jsonResponse(200, [
            'results' => [['id' => 'u1'], ['id' => 'u2']],
            'paging' => ['next' => ['after' => 'page2']],
        ]),
        jsonResponse(200, [
            'results' => [['id' => 'u3']],
        ]),
    ]);

    $items = $client->settings()->users()->all()->all();

    expect($items)->toBe([['id' => 'u1'], ['id' => 'u2'], ['id' => 'u3']]);
});

it('get() sends GET /settings/users/2026-09/{userId}', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['id' => 'u1', 'email' => 'alice@example.com']),
    ]);

    $result = $client->settings()->users()->get('u1');

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('GET');
    expect($request->getUri()->getPath())->toBe('/settings/users/2026-09/u1');
    expect($result)->toBe(['id' => 'u1', 'email' => 'alice@example.com']);
});

it('roles() sends GET /settings/users/2026-09/roles', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['results' => [['id' => 'role-1', 'name' => 'Admin']]]),
    ]);

    $result = $client->settings()->users()->roles();

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('GET');
    expect($request->getUri()->getPath())->toBe('/settings/users/2026-09/roles');
    expect($result)->toBe(['results' => [['id' => 'role-1', 'name' => 'Admin']]]);
});

it('seats() sends GET /settings/users/2026-09/seats', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['results' => [['type' => 'core', 'count' => 5]]]),
    ]);

    $result = $client->settings()->users()->seats();

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('GET');
    expect($request->getUri()->getPath())->toBe('/settings/users/2026-09/seats');
    expect($result)->toBe(['results' => [['type' => 'core', 'count' => 5]]]);
});

it('teams() sends GET /settings/users/2026-09/teams', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['results' => [['id' => 'team-1', 'name' => 'Sales']]]),
    ]);

    $result = $client->settings()->users()->teams();

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('GET');
    expect($request->getUri()->getPath())->toBe('/settings/users/2026-09/teams');
    expect($result)->toBe(['results' => [['id' => 'team-1', 'name' => 'Sales']]]);
});

it('version override changes the date segment in the path', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['results' => []]),
    ]);

    $client->settings()->users('2026-03')->list();

    expect($mock->getLastRequest()->getUri()->getPath())->toBe('/settings/users/2026-03');
});
