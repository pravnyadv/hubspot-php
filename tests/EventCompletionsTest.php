<?php

declare(strict_types=1);

it('send() sends POST /events/2026-09/send with body', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, []),
    ]);

    $body = ['eventName' => 'e_clicked_button', 'objectId' => '123', 'objectType' => 'contact'];
    $result = $client->events()->completions()->send($body);

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('POST');
    expect($request->getUri()->getPath())->toBe('/events/2026-09/send');
    expect(json_decode((string) $request->getBody(), true))->toBe($body);
    expect($result)->toBe([]);
});

it('batchSend() sends POST /events/2026-09/send/batch wrapping inputs', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['status' => 'COMPLETE']),
    ]);

    $inputs = [
        ['eventName' => 'e_clicked_button', 'objectId' => '1', 'objectType' => 'contact'],
        ['eventName' => 'e_clicked_button', 'objectId' => '2', 'objectType' => 'contact'],
    ];
    $result = $client->events()->completions()->batchSend($inputs);

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('POST');
    expect($request->getUri()->getPath())->toBe('/events/2026-09/send/batch');
    expect(json_decode((string) $request->getBody(), true))->toBe(['inputs' => $inputs]);
    expect($result)->toBe(['status' => 'COMPLETE']);
});

it('version override changes the date segment in the path', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, []),
    ]);

    $client->events()->completions('2026-03')->send(['eventName' => 'e_test', 'objectId' => '1', 'objectType' => 'contact']);

    expect($mock->getLastRequest()->getUri()->getPath())->toBe('/events/2026-03/send');
});
