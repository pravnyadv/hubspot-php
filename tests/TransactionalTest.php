<?php

declare(strict_types=1);

it('send() sends POST /marketing/transactional/2026-09/single-email/send', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['id' => 'txn-456', 'status' => 'queued']),
    ]);

    $result = $client->marketing()->transactional()->send([
        'emailId' => 67890,
        'message' => ['to' => 'customer@example.com'],
    ]);

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('POST');
    expect($request->getUri()->getPath())->toBe('/marketing/transactional/2026-09/single-email/send');
    expect(json_decode((string) $request->getBody(), true))->toBe([
        'emailId' => 67890,
        'message' => ['to' => 'customer@example.com'],
    ]);
    expect($result)->toBe(['id' => 'txn-456', 'status' => 'queued']);
});

it('listSmtpTokens() sends GET /marketing/transactional/2026-09/smtp-tokens', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['results' => [['id' => 'token-1']]]),
    ]);

    $result = $client->marketing()->transactional()->listSmtpTokens();

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('GET');
    expect($request->getUri()->getPath())->toBe('/marketing/transactional/2026-09/smtp-tokens');
    expect($result)->toBe(['results' => [['id' => 'token-1']]]);
});

it('listSmtpTokens() passes query params and omits nulls', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['results' => []]),
    ]);

    $client->marketing()->transactional()->listSmtpTokens(['campaignName' => 'My Campaign', 'after' => null]);

    parse_str($mock->getLastRequest()->getUri()->getQuery(), $query);
    expect($query['campaignName'])->toBe('My Campaign');
    expect($query)->not->toHaveKey('after');
});

it('createSmtpToken() sends POST /marketing/transactional/2026-09/smtp-tokens with body', function () {
    [$client, $mock] = mockClient([
        jsonResponse(201, ['id' => 'token-new']),
    ]);

    $result = $client->marketing()->transactional()->createSmtpToken(['campaignName' => 'My Campaign']);

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('POST');
    expect($request->getUri()->getPath())->toBe('/marketing/transactional/2026-09/smtp-tokens');
    expect(json_decode((string) $request->getBody(), true))->toBe(['campaignName' => 'My Campaign']);
    expect($result)->toBe(['id' => 'token-new']);
});

it('getSmtpToken() sends GET /marketing/transactional/2026-09/smtp-tokens/{tokenId}', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['id' => 'token-1', 'campaignName' => 'My Campaign']),
    ]);

    $result = $client->marketing()->transactional()->getSmtpToken('token-1');

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('GET');
    expect($request->getUri()->getPath())->toBe('/marketing/transactional/2026-09/smtp-tokens/token-1');
    expect($result)->toBe(['id' => 'token-1', 'campaignName' => 'My Campaign']);
});

it('archiveSmtpToken() sends DELETE /marketing/transactional/2026-09/smtp-tokens/{tokenId}', function () {
    [$client, $mock] = mockClient([
        jsonResponse(204),
    ]);

    $client->marketing()->transactional()->archiveSmtpToken('token-1');

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('DELETE');
    expect($request->getUri()->getPath())->toBe('/marketing/transactional/2026-09/smtp-tokens/token-1');
});

it('resetSmtpTokenPassword() sends POST /marketing/transactional/2026-09/smtp-tokens/{tokenId}/password-reset', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['id' => 'token-1', 'password' => 'new-pass']),
    ]);

    $result = $client->marketing()->transactional()->resetSmtpTokenPassword('token-1');

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('POST');
    expect($request->getUri()->getPath())->toBe('/marketing/transactional/2026-09/smtp-tokens/token-1/password-reset');
    expect($result)->toBe(['id' => 'token-1', 'password' => 'new-pass']);
});

it('version override changes the date segment in the path', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['id' => 'txn-456']),
    ]);

    $client->marketing()->transactional('2026-03')->send(['emailId' => 99]);

    expect($mock->getLastRequest()->getUri()->getPath())->toBe('/marketing/transactional/2026-03/single-email/send');
});
