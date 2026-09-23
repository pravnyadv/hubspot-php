<?php

declare(strict_types=1);

it('list() sends GET /events/event-occurrences/2026-09', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['results' => [['objectType' => 'contact', 'objectId' => '1']]]),
    ]);

    $result = $client->events()->query()->list();

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('GET');
    expect($request->getUri()->getPath())->toBe('/events/event-occurrences/2026-09');
    expect($result)->toBe(['results' => [['objectType' => 'contact', 'objectId' => '1']]]);
});

it('list() passes query params and omits nulls', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['results' => []]),
    ]);

    $client->events()->query()->list(['objectType' => 'contact', 'after' => null]);

    parse_str($mock->getLastRequest()->getUri()->getQuery(), $query);
    expect($query['objectType'])->toBe('contact');
    expect($query)->not->toHaveKey('after');
});

it('all() paginates across two pages', function () {
    [$client] = mockClient([
        jsonResponse(200, [
            'results' => [['objectId' => '1'], ['objectId' => '2']],
            'paging' => ['next' => ['after' => 'cur2']],
        ]),
        jsonResponse(200, [
            'results' => [['objectId' => '3']],
        ]),
    ]);

    $items = $client->events()->query()->all()->all();

    expect($items)->toBe([['objectId' => '1'], ['objectId' => '2'], ['objectId' => '3']]);
});

it('eventTypes() sends GET /events/event-occurrences/2026-09/event-types', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['results' => [['name' => 'e_pageview']]]),
    ]);

    $result = $client->events()->query()->eventTypes();

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('GET');
    expect($request->getUri()->getPath())->toBe('/events/event-occurrences/2026-09/event-types');
    expect($result)->toBe(['results' => [['name' => 'e_pageview']]]);
});

it('version override changes the date segment in the path', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['results' => []]),
    ]);

    $client->events()->query('2026-03')->list();

    expect($mock->getLastRequest()->getUri()->getPath())->toBe('/events/event-occurrences/2026-03');
});
