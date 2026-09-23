<?php

declare(strict_types=1);

it('list() sends GET /cms/domains/2026-09', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['results' => [['id' => 'domain-1', 'domain' => 'example.com']]]),
    ]);

    $result = $client->cms()->domains()->list();

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('GET');
    expect($request->getUri()->getPath())->toBe('/cms/domains/2026-09');
    expect($result)->toBe(['results' => [['id' => 'domain-1', 'domain' => 'example.com']]]);
});

it('list() passes query params and omits nulls', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['results' => []]),
    ]);

    $client->cms()->domains()->list(['isResolving' => true, 'after' => null]);

    parse_str($mock->getLastRequest()->getUri()->getQuery(), $q);
    expect($q)->toHaveKey('isResolving');
    expect($q)->not->toHaveKey('after');
});

it('all() paginates across two pages', function () {
    [$client] = mockClient([
        jsonResponse(200, [
            'results' => [['id' => 'domain-1']],
            'paging' => ['next' => ['after' => 'cursor2']],
        ]),
        jsonResponse(200, [
            'results' => [['id' => 'domain-2']],
        ]),
    ]);

    $items = $client->cms()->domains()->all()->all();

    expect($items)->toBe([['id' => 'domain-1'], ['id' => 'domain-2']]);
});

it('get() sends GET /cms/domains/2026-09/{domainId}', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['id' => 'domain-1', 'domain' => 'example.com']),
    ]);

    $result = $client->cms()->domains()->get('domain-1');

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('GET');
    expect($request->getUri()->getPath())->toBe('/cms/domains/2026-09/domain-1');
    expect($result)->toBe(['id' => 'domain-1', 'domain' => 'example.com']);
});

it('version override changes the date segment in the path', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['results' => []]),
    ]);

    $client->cms()->domains('2026-03')->list();

    expect($mock->getLastRequest()->getUri()->getPath())->toBe('/cms/domains/2026-03');
});
