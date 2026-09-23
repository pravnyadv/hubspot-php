<?php

declare(strict_types=1);

it('forUser() sends GET /business-units/public/2026-09/business-units/user/{userId}', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['results' => [['id' => 'bu-1', 'name' => 'EMEA']]]),
    ]);

    $result = $client->settings()->businessUnits()->forUser('user-abc');

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('GET');
    expect($request->getUri()->getPath())->toBe('/business-units/public/2026-09/business-units/user/user-abc');
    expect($result)->toBe(['results' => [['id' => 'bu-1', 'name' => 'EMEA']]]);
});

it('version override changes the date segment in the path', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['results' => []]),
    ]);

    $client->settings()->businessUnits('2026-03')->forUser('user-abc');

    expect($mock->getLastRequest()->getUri()->getPath())->toBe('/business-units/public/2026-03/business-units/user/user-abc');
});
