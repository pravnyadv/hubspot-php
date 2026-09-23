<?php

declare(strict_types=1);

it('list() sends GET /marketing/marketing-events/2026-09', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['results' => [['id' => 'evt-1']]]),
    ]);

    $result = $client->marketing()->events()->list();

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('GET');
    expect($request->getUri()->getPath())->toBe('/marketing/marketing-events/2026-09');
    expect($result)->toBe(['results' => [['id' => 'evt-1']]]);
});

it('list() passes query params and omits nulls', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['results' => []]),
    ]);

    $client->marketing()->events()->list(['limit' => 20, 'after' => null]);

    parse_str($mock->getLastRequest()->getUri()->getQuery(), $query);
    expect($query['limit'])->toBe('20');
    expect($query)->not->toHaveKey('after');
});

it('all() paginates across two pages via the Paginator', function () {
    [$client] = mockClient([
        jsonResponse(200, [
            'results' => [['id' => 'evt-1'], ['id' => 'evt-2']],
            'paging' => ['next' => ['after' => 'cursor2']],
        ]),
        jsonResponse(200, [
            'results' => [['id' => 'evt-3']],
        ]),
    ]);

    $items = $client->marketing()->events()->all()->all();

    expect($items)->toBe([['id' => 'evt-1'], ['id' => 'evt-2'], ['id' => 'evt-3']]);
});

it('get() sends GET /marketing/marketing-events/2026-09/events/{externalEventId}', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['id' => 'evt-99', 'name' => 'Test Event']),
    ]);

    $result = $client->marketing()->events()->get('evt-99');

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('GET');
    expect($request->getUri()->getPath())->toBe('/marketing/marketing-events/2026-09/events/evt-99');
    expect($result)->toBe(['id' => 'evt-99', 'name' => 'Test Event']);
});

it('search() sends GET /marketing/marketing-events/2026-09/events/search', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['results' => [['id' => 'evt-1']]]),
    ]);

    $result = $client->marketing()->events()->search('webinar');

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('GET');
    expect($request->getUri()->getPath())->toBe('/marketing/marketing-events/2026-09/events/search');
    parse_str($request->getUri()->getQuery(), $query);
    expect($query['q'])->toBe('webinar');
    expect($result)->toBe(['results' => [['id' => 'evt-1']]]);
});

it('upsert() sends POST /marketing/marketing-events/2026-09/events/upsert with inputs wrapper', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['status' => 'COMPLETE']),
    ]);

    $inputs = [['externalEventId' => 'ext-1', 'eventName' => 'Webinar']];
    $client->marketing()->events()->upsert($inputs);

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('POST');
    expect($request->getUri()->getPath())->toBe('/marketing/marketing-events/2026-09/events/upsert');
    expect(json_decode((string) $request->getBody(), true))->toBe(['inputs' => $inputs]);
});

it('batchDelete() sends POST /marketing/marketing-events/2026-09/events/delete', function () {
    [$client, $mock] = mockClient([
        jsonResponse(204, ''),
    ]);

    $client->marketing()->events()->batchDelete([['externalEventId' => 'ext-1']]);

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('POST');
    expect($request->getUri()->getPath())->toBe('/marketing/marketing-events/2026-09/events/delete');
});

it('cancel() sends POST /marketing/marketing-events/2026-09/events/{externalEventId}/cancel', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['id' => 'evt-1', 'status' => 'CANCELLED']),
    ]);

    $result = $client->marketing()->events()->cancel('evt-1');

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('POST');
    expect($request->getUri()->getPath())->toBe('/marketing/marketing-events/2026-09/events/evt-1/cancel');
    expect($result)->toBe(['id' => 'evt-1', 'status' => 'CANCELLED']);
});

it('complete() sends POST /marketing/marketing-events/2026-09/events/{externalEventId}/complete', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['id' => 'evt-1', 'status' => 'COMPLETED']),
    ]);

    $client->marketing()->events()->complete('evt-1');

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('POST');
    expect($request->getUri()->getPath())->toBe('/marketing/marketing-events/2026-09/events/evt-1/complete');
});

it('batchArchive() sends POST /marketing/marketing-events/2026-09/batch/archive', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['status' => 'COMPLETE']),
    ]);

    $inputs = [['id' => 'obj-1']];
    $client->marketing()->events()->batchArchive($inputs);

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('POST');
    expect($request->getUri()->getPath())->toBe('/marketing/marketing-events/2026-09/batch/archive');
    expect(json_decode((string) $request->getBody(), true))->toBe(['inputs' => $inputs]);
});

it('batchUpdate() sends POST /marketing/marketing-events/2026-09/batch/update', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['status' => 'COMPLETE']),
    ]);

    $inputs = [['id' => 'obj-1', 'properties' => ['eventName' => 'New Name']]];
    $client->marketing()->events()->batchUpdate($inputs);

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('POST');
    expect($request->getUri()->getPath())->toBe('/marketing/marketing-events/2026-09/batch/update');
    expect(json_decode((string) $request->getBody(), true))->toBe(['inputs' => $inputs]);
});

it('recordAttendance() sends POST /marketing/marketing-events/2026-09/attendance/{eventId}/{state}/create', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['status' => 'COMPLETE']),
    ]);

    $body = ['inputs' => [['vid' => 12345]]];
    $client->marketing()->events()->recordAttendance('evt-1', 'ATTENDED', $body);

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('POST');
    expect($request->getUri()->getPath())->toBe('/marketing/marketing-events/2026-09/attendance/evt-1/ATTENDED/create');
});

it('recordAttendanceByEmail() sends POST .../attendance/{eventId}/{state}/email-create', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['status' => 'COMPLETE']),
    ]);

    $client->marketing()->events()->recordAttendanceByEmail('evt-1', 'ATTENDED', ['inputs' => []]);

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('POST');
    expect($request->getUri()->getPath())->toBe('/marketing/marketing-events/2026-09/attendance/evt-1/ATTENDED/email-create');
});

it('participations() sends GET /marketing/marketing-events/2026-09/participations/{marketingEventId}', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['results' => [['vid' => 1]]]),
    ]);

    $result = $client->marketing()->events()->participations('me-42');

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('GET');
    expect($request->getUri()->getPath())->toBe('/marketing/marketing-events/2026-09/participations/me-42');
    expect($result)->toBe(['results' => [['vid' => 1]]]);
});

it('participationsBreakdown() sends GET .../participations/{marketingEventId}/breakdown', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['results' => []]),
    ]);

    $client->marketing()->events()->participationsBreakdown('me-42');

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('GET');
    expect($request->getUri()->getPath())->toBe('/marketing/marketing-events/2026-09/participations/me-42/breakdown');
});

it('getSettings() sends GET /marketing/marketing-events/2026-09/{appId}/settings', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['applicationId' => 12345]),
    ]);

    $result = $client->marketing()->events()->getSettings('12345');

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('GET');
    expect($request->getUri()->getPath())->toBe('/marketing/marketing-events/2026-09/12345/settings');
    expect($result)->toBe(['applicationId' => 12345]);
});

it('updateSettings() sends POST /marketing/marketing-events/2026-09/{appId}/settings', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['applicationId' => 12345]),
    ]);

    $client->marketing()->events()->updateSettings('12345', ['eventCompletionRegistrationEnabled' => true]);

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('POST');
    expect($request->getUri()->getPath())->toBe('/marketing/marketing-events/2026-09/12345/settings');
    expect(json_decode((string) $request->getBody(), true))->toBe(['eventCompletionRegistrationEnabled' => true]);
});

it('version override changes the date segment in the path', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['results' => []]),
    ]);

    $client->marketing()->events('2026-03')->list();

    expect($mock->getLastRequest()->getUri()->getPath())->toBe('/marketing/marketing-events/2026-03');
});
