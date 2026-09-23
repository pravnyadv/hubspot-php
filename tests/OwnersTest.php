<?php

declare(strict_types=1);

it('page() sends GET /crm/owners/2026-09 with limit and after', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['results' => [['id' => '1']]]),
    ]);

    $result = $client->crm()->owners()->page(50, 'cursor-abc');

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('GET');
    expect($request->getUri()->getPath())->toBe('/crm/owners/2026-09');
    parse_str($request->getUri()->getQuery(), $query);
    expect($query['limit'])->toBe('50');
    expect($query['after'])->toBe('cursor-abc');
    expect($result)->toBe(['results' => [['id' => '1']]]);
});

it('page() omits null after from the query string', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['results' => []]),
    ]);

    $client->crm()->owners()->page();

    parse_str($mock->getLastRequest()->getUri()->getQuery(), $query);
    expect($query)->not->toHaveKey('after');
    expect($query['limit'])->toBe('100');
});

it('get() sends GET /crm/owners/2026-09/{ownerId}', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['id' => '42', 'email' => 'owner@example.com']),
    ]);

    $result = $client->crm()->owners()->get('42');

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('GET');
    expect($request->getUri()->getPath())->toBe('/crm/owners/2026-09/42');
    expect($result)->toBe(['id' => '42', 'email' => 'owner@example.com']);
});

it('all() paginates across two pages via the Paginator', function () {
    [$client] = mockClient([
        jsonResponse(200, [
            'results' => [['id' => '1'], ['id' => '2']],
            'paging' => ['next' => ['after' => 'page2cursor']],
        ]),
        jsonResponse(200, [
            'results' => [['id' => '3']],
        ]),
    ]);

    $items = $client->crm()->owners()->all()->all();

    expect($items)->toBe([['id' => '1'], ['id' => '2'], ['id' => '3']]);
});

it('version override changes the date segment in the path', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['results' => []]),
    ]);

    $client->crm()->owners('2026-03')->page();

    expect($mock->getLastRequest()->getUri()->getPath())->toBe('/crm/owners/2026-03');
});
