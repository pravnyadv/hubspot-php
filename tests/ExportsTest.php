<?php

declare(strict_types=1);

it('startAsync() sends POST /crm/exports/2026-09/export/async', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['taskId' => 'task-123']),
    ]);

    $result = $client->crm()->exports()->startAsync(['exportType' => 'VIEW', 'objectType' => 'contacts']);

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('POST');
    expect($request->getUri()->getPath())->toBe('/crm/exports/2026-09/export/async');
    $body = json_decode((string) $request->getBody(), true);
    expect($body['exportType'])->toBe('VIEW');
    expect($result)->toBe(['taskId' => 'task-123']);
});

it('taskStatus() sends GET /crm/exports/2026-09/export/async/tasks/{taskId}/status', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['status' => 'COMPLETE', 'result' => ['url' => 'https://example.com/file.csv']]),
    ]);

    $result = $client->crm()->exports()->taskStatus('task-123');

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('GET');
    expect($request->getUri()->getPath())->toBe('/crm/exports/2026-09/export/async/tasks/task-123/status');
    expect($result['status'])->toBe('COMPLETE');
});

it('get() sends GET /crm/exports/2026-09/export/{exportId}', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['id' => 'export-1', 'state' => 'COMPLETE']),
    ]);

    $result = $client->crm()->exports()->get('export-1');

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('GET');
    expect($request->getUri()->getPath())->toBe('/crm/exports/2026-09/export/export-1');
    expect($result)->toBe(['id' => 'export-1', 'state' => 'COMPLETE']);
});

it('version override changes the date segment in the path', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['taskId' => 'task-xyz']),
    ]);

    $client->crm()->exports('2026-03')->startAsync(['exportType' => 'VIEW', 'objectType' => 'contacts']);

    expect($mock->getLastRequest()->getUri()->getPath())->toBe('/crm/exports/2026-03/export/async');
});
