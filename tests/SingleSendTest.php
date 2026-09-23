<?php

declare(strict_types=1);

it('send() sends POST /marketing/email-campaigns/2026-09/single-send with body', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['id' => 'send-123', 'status' => 'queued']),
    ]);

    $result = $client->marketing()->singleSend()->send([
        'emailId' => 12345,
        'message' => ['to' => 'user@example.com'],
    ]);

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('POST');
    expect($request->getUri()->getPath())->toBe('/marketing/email-campaigns/2026-09/single-send');
    expect(json_decode((string) $request->getBody(), true))->toBe([
        'emailId' => 12345,
        'message' => ['to' => 'user@example.com'],
    ]);
    expect($result)->toBe(['id' => 'send-123', 'status' => 'queued']);
});

it('version override changes the date segment in the path', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['id' => 'send-123']),
    ]);

    $client->marketing()->singleSend('2026-03')->send(['emailId' => 99]);

    expect($mock->getLastRequest()->getUri()->getPath())->toBe('/marketing/email-campaigns/2026-03/single-send');
});
