<?php

declare(strict_types=1);

it('list() sends GET /settings/teams/2026-09', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['results' => [['id' => 'team-1', 'name' => 'Sales']]]),
    ]);

    $result = $client->settings()->teams()->list();

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('GET');
    expect($request->getUri()->getPath())->toBe('/settings/teams/2026-09');
    expect($result)->toBe(['results' => [['id' => 'team-1', 'name' => 'Sales']]]);
});

it('list() forwards query params and omits nulls', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['results' => []]),
    ]);

    $client->settings()->teams()->list(['after' => 'cursor-1', 'limit' => null]);

    parse_str($mock->getLastRequest()->getUri()->getQuery(), $query);
    expect($query['after'])->toBe('cursor-1');
    expect($query)->not->toHaveKey('limit');
});

it('all() paginates across two pages via Paginator', function () {
    [$client] = mockClient([
        jsonResponse(200, [
            'results' => [['id' => 'team-1'], ['id' => 'team-2']],
            'paging' => ['next' => ['after' => 'page2']],
        ]),
        jsonResponse(200, [
            'results' => [['id' => 'team-3']],
        ]),
    ]);

    $items = $client->settings()->teams()->all()->all();

    expect($items)->toBe([['id' => 'team-1'], ['id' => 'team-2'], ['id' => 'team-3']]);
});

it('get() sends GET /settings/teams/2026-09/{teamId}', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['id' => 'team-1', 'name' => 'Sales']),
    ]);

    $result = $client->settings()->teams()->get('team-1');

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('GET');
    expect($request->getUri()->getPath())->toBe('/settings/teams/2026-09/team-1');
    expect($result)->toBe(['id' => 'team-1', 'name' => 'Sales']);
});

it('members() sends GET /settings/teams/2026-09/{teamId}/members', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['results' => [['userId' => 'u1']]]),
    ]);

    $result = $client->settings()->teams()->members('team-1');

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('GET');
    expect($request->getUri()->getPath())->toBe('/settings/teams/2026-09/team-1/members');
    expect($result)->toBe(['results' => [['userId' => 'u1']]]);
});

it('batchMembers() sends POST /settings/teams/2026-09/{teamId}/members/batch with inputs', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['status' => 'COMPLETE']),
    ]);

    $result = $client->settings()->teams()->batchMembers('team-1', ['u1', 'u2']);

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('POST');
    expect($request->getUri()->getPath())->toBe('/settings/teams/2026-09/team-1/members/batch');
    $body = json_decode((string) $request->getBody(), true);
    expect($body['inputs'])->toBe(['u1', 'u2']);
    expect($result)->toBe(['status' => 'COMPLETE']);
});

it('removeMember() sends DELETE /settings/teams/2026-09/{teamId}/members/{userId}', function () {
    [$client, $mock] = mockClient([
        jsonResponse(204),
    ]);

    $client->settings()->teams()->removeMember('team-1', 'u1');

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('DELETE');
    expect($request->getUri()->getPath())->toBe('/settings/teams/2026-09/team-1/members/u1');
});

it('version override changes the date segment in the path', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['results' => []]),
    ]);

    $client->settings()->teams('2026-03')->list();

    expect($mock->getLastRequest()->getUri()->getPath())->toBe('/settings/teams/2026-03');
});
