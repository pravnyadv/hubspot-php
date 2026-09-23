<?php

declare(strict_types=1);

it('createEvent() sends POST /integrators/timeline/2026-09/events with the event body', function () {
    $event = ['eventTemplateId' => 'tpl-1', 'objectId' => '42', 'tokens' => ['name' => 'Alice']];

    [$client, $mock] = mockClient([
        jsonResponse(201, ['id' => 'evt-abc']),
    ]);

    $result = $client->crm()->timeline()->createEvent($event);

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('POST');
    expect($request->getUri()->getPath())->toBe('/integrators/timeline/2026-09/events');
    $body = json_decode((string) $request->getBody(), true);
    expect($body)->toBe($event);
    expect($result)->toBe(['id' => 'evt-abc']);
});

it('createBatch() sends POST /integrators/timeline/2026-09/events/batch with the payload', function () {
    $payload = ['inputs' => [['eventTemplateId' => 'tpl-1', 'objectId' => '1'], ['eventTemplateId' => 'tpl-1', 'objectId' => '2']]];

    [$client, $mock] = mockClient([
        jsonResponse(201, ['status' => 'COMPLETE', 'results' => []]),
    ]);

    $result = $client->crm()->timeline()->createBatch($payload);

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('POST');
    expect($request->getUri()->getPath())->toBe('/integrators/timeline/2026-09/events/batch');
    $body = json_decode((string) $request->getBody(), true);
    expect($body)->toBe($payload);
    expect($result)->toBe(['status' => 'COMPLETE', 'results' => []]);
});

it('version override changes the date segment in all timeline paths', function () {
    [$client, $mock] = mockClient([
        jsonResponse(201, ['id' => 'evt-xyz']),
    ]);

    $client->crm()->timeline('2026-03')->createEvent(['eventTemplateId' => 'tpl-1', 'objectId' => '9']);

    expect($mock->getLastRequest()->getUri()->getPath())->toBe('/integrators/timeline/2026-03/events');
});
