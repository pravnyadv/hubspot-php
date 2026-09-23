<?php

declare(strict_types=1);

it('list() sends GET /events/2026-09/event-definitions', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['results' => [['name' => 'e_clicked_button']]]),
    ]);

    $result = $client->events()->definitions()->list();

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('GET');
    expect($request->getUri()->getPath())->toBe('/events/2026-09/event-definitions');
    expect($result)->toBe(['results' => [['name' => 'e_clicked_button']]]);
});

it('all() paginates across two pages', function () {
    [$client] = mockClient([
        jsonResponse(200, [
            'results' => [['name' => 'e_a'], ['name' => 'e_b']],
            'paging' => ['next' => ['after' => 'p2']],
        ]),
        jsonResponse(200, [
            'results' => [['name' => 'e_c']],
        ]),
    ]);

    $items = $client->events()->definitions()->all()->all();

    expect($items)->toBe([['name' => 'e_a'], ['name' => 'e_b'], ['name' => 'e_c']]);
});

it('get() sends GET /events/2026-09/event-definitions/{eventName}', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['name' => 'e_clicked_button', 'label' => 'Clicked Button']),
    ]);

    $result = $client->events()->definitions()->get('e_clicked_button');

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('GET');
    expect($request->getUri()->getPath())->toBe('/events/2026-09/event-definitions/e_clicked_button');
    expect($result)->toBe(['name' => 'e_clicked_button', 'label' => 'Clicked Button']);
});

it('create() sends POST /events/2026-09/event-definitions with body', function () {
    [$client, $mock] = mockClient([
        jsonResponse(201, ['name' => 'e_new_event']),
    ]);

    $result = $client->events()->definitions()->create(['name' => 'e_new_event', 'label' => 'New Event']);

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('POST');
    expect($request->getUri()->getPath())->toBe('/events/2026-09/event-definitions');
    expect(json_decode((string) $request->getBody(), true))->toBe(['name' => 'e_new_event', 'label' => 'New Event']);
    expect($result)->toBe(['name' => 'e_new_event']);
});

it('update() sends PATCH /events/2026-09/event-definitions/{eventName}', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['name' => 'e_clicked_button', 'label' => 'Updated']),
    ]);

    $result = $client->events()->definitions()->update('e_clicked_button', ['label' => 'Updated']);

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('PATCH');
    expect($request->getUri()->getPath())->toBe('/events/2026-09/event-definitions/e_clicked_button');
    expect(json_decode((string) $request->getBody(), true))->toBe(['label' => 'Updated']);
    expect($result)->toBe(['name' => 'e_clicked_button', 'label' => 'Updated']);
});

it('archive() sends DELETE /events/2026-09/event-definitions/{eventName}', function () {
    [$client, $mock] = mockClient([
        jsonResponse(204),
    ]);

    $client->events()->definitions()->archive('e_clicked_button');

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('DELETE');
    expect($request->getUri()->getPath())->toBe('/events/2026-09/event-definitions/e_clicked_button');
});

it('createProperty() sends POST /events/2026-09/event-definitions/{eventName}/property', function () {
    [$client, $mock] = mockClient([
        jsonResponse(201, ['name' => 'button_id']),
    ]);

    $result = $client->events()->definitions()->createProperty('e_clicked_button', ['name' => 'button_id', 'type' => 'string']);

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('POST');
    expect($request->getUri()->getPath())->toBe('/events/2026-09/event-definitions/e_clicked_button/property');
    expect($result)->toBe(['name' => 'button_id']);
});

it('updateProperty() sends PATCH /events/2026-09/event-definitions/{eventName}/property/{propertyName}', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['name' => 'button_id', 'label' => 'Button ID']),
    ]);

    $result = $client->events()->definitions()->updateProperty('e_clicked_button', 'button_id', ['label' => 'Button ID']);

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('PATCH');
    expect($request->getUri()->getPath())->toBe('/events/2026-09/event-definitions/e_clicked_button/property/button_id');
    expect($result)->toBe(['name' => 'button_id', 'label' => 'Button ID']);
});

it('archiveProperty() sends DELETE /events/2026-09/event-definitions/{eventName}/property/{propertyName}', function () {
    [$client, $mock] = mockClient([
        jsonResponse(204),
    ]);

    $client->events()->definitions()->archiveProperty('e_clicked_button', 'button_id');

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('DELETE');
    expect($request->getUri()->getPath())->toBe('/events/2026-09/event-definitions/e_clicked_button/property/button_id');
});

it('version override changes the date segment in the path', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['results' => []]),
    ]);

    $client->events()->definitions('2026-03')->list();

    expect($mock->getLastRequest()->getUri()->getPath())->toBe('/events/2026-03/event-definitions');
});
