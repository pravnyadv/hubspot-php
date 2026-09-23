<?php

declare(strict_types=1);

it('details() sends GET /account-info/2026-09/details', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['portalId' => 12345, 'timeZone' => 'UTC']),
    ]);

    $result = $client->account()->details();

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('GET');
    expect($request->getUri()->getPath())->toBe('/account-info/2026-09/details');
    expect($request->getUri()->getQuery())->toBe('');
    expect($result)->toBe(['portalId' => 12345, 'timeZone' => 'UTC']);
});

it('version override changes the date segment in the path', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['portalId' => 12345]),
    ]);

    $client->account('2026-03')->details();

    expect($mock->getLastRequest()->getUri()->getPath())->toBe('/account-info/2026-03/details');
});
