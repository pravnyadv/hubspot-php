<?php

declare(strict_types=1);

it('calendar() sends POST /scheduler/2026-09/meetings/calendar', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['id' => 'evt-1', 'startTime' => '2026-09-24T10:00:00Z']),
    ]);

    $body = ['startTime' => '2026-09-24T10:00:00Z'];
    $result = $client->scheduler()->calendar('user-1', $body);

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('POST');
    expect($request->getUri()->getPath())->toBe('/scheduler/2026-09/meetings/calendar');
    parse_str($request->getUri()->getQuery(), $query);
    expect($query['organizerUserId'])->toBe('user-1');
    expect(json_decode((string) $request->getBody(), true))->toBe($body);
    expect($result)->toBe(['id' => 'evt-1', 'startTime' => '2026-09-24T10:00:00Z']);
});

it('list() sends GET /scheduler/2026-09/meetings/meeting-links', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['results' => [['id' => 'link-1', 'slug' => 'my-meeting']]]),
    ]);

    $result = $client->scheduler()->list();

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('GET');
    expect($request->getUri()->getPath())->toBe('/scheduler/2026-09/meetings/meeting-links');
    expect($result)->toBe(['results' => [['id' => 'link-1', 'slug' => 'my-meeting']]]);
});

it('all() paginates meeting links across two pages', function () {
    [$client] = mockClient([
        jsonResponse(200, [
            'results' => [['id' => 'link-1']],
            'paging' => ['next' => ['after' => 'cursor-2']],
        ]),
        jsonResponse(200, [
            'results' => [['id' => 'link-2']],
        ]),
    ]);

    $items = $client->scheduler()->all()->all();

    expect($items)->toBe([['id' => 'link-1'], ['id' => 'link-2']]);
});

it('get() sends GET /scheduler/2026-09/meetings/meeting-links/book/{slug}', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['id' => 'link-1', 'slug' => 'my-meeting', 'name' => 'My Meeting']),
    ]);

    $result = $client->scheduler()->get('my-meeting');

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('GET');
    expect($request->getUri()->getPath())->toBe('/scheduler/2026-09/meetings/meeting-links/book/my-meeting');
    expect($result)->toBe(['id' => 'link-1', 'slug' => 'my-meeting', 'name' => 'My Meeting']);
});

it('availability() sends GET /scheduler/2026-09/meetings/meeting-links/book/availability-page/{slug}', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['linkId' => 'link-1', 'availableSlots' => []]),
    ]);

    $result = $client->scheduler()->availability('my-meeting');

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('GET');
    expect($request->getUri()->getPath())->toBe('/scheduler/2026-09/meetings/meeting-links/book/availability-page/my-meeting');
    expect($result)->toBe(['linkId' => 'link-1', 'availableSlots' => []]);
});

it('book() sends POST /scheduler/2026-09/meetings/meeting-links/book with body', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['id' => 'booking-1', 'startTime' => '2026-09-24T10:00:00Z']),
    ]);

    $body = ['slug' => 'my-meeting', 'startTime' => '2026-09-24T10:00:00Z', 'firstName' => 'Alice'];
    $result = $client->scheduler()->book($body);

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('POST');
    expect($request->getUri()->getPath())->toBe('/scheduler/2026-09/meetings/meeting-links/book');
    expect(json_decode((string) $request->getBody(), true))->toBe($body);
    expect($result)->toBe(['id' => 'booking-1', 'startTime' => '2026-09-24T10:00:00Z']);
});

it('version override changes the date segment in the path', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['results' => []]),
    ]);

    $client->scheduler('2026-03')->list();

    expect($mock->getLastRequest()->getUri()->getPath())->toBe('/scheduler/2026-03/meetings/meeting-links');
});
