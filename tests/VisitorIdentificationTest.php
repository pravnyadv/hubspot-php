<?php

declare(strict_types=1);

it('createToken() sends POST /visitor-identification/2026-09/tokens/create', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['token' => 'eyJhbGci.abc.def']),
    ]);

    $result = $client->conversations()->visitorIdentification()->createToken(['email' => 'contact@example.com']);

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('POST');
    expect($request->getUri()->getPath())->toBe('/visitor-identification/2026-09/tokens/create');
    expect(json_decode((string) $request->getBody(), true))->toBe(['email' => 'contact@example.com']);
    expect($result)->toBe(['token' => 'eyJhbGci.abc.def']);
});

it('createToken() sends full body including name fields', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['token' => 'tok-xyz']),
    ]);

    $client->conversations()->visitorIdentification()->createToken([
        'email' => 'contact@example.com',
        'firstName' => 'Ada',
        'lastName' => 'Lovelace',
    ]);

    $body = json_decode((string) $mock->getLastRequest()->getBody(), true);
    expect($body)->toBe([
        'email' => 'contact@example.com',
        'firstName' => 'Ada',
        'lastName' => 'Lovelace',
    ]);
});

it('version override changes the date segment in the path', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['token' => 'tok-old']),
    ]);

    $client->conversations()->visitorIdentification('2026-03')->createToken(['email' => 'x@example.com']);

    expect($mock->getLastRequest()->getUri()->getPath())
        ->toBe('/visitor-identification/2026-03/tokens/create');
});
