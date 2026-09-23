<?php

declare(strict_types=1);

it('list() sends GET /commerce/payment-links/2026-09/payment-links', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['results' => [['id' => 'link-1']]]),
    ]);

    $result = $client->commerce()->paymentLinks()->list();

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('GET');
    expect($request->getUri()->getPath())->toBe('/commerce/payment-links/2026-09/payment-links');
    expect($result)->toBe(['results' => [['id' => 'link-1']]]);
});

it('list() passes query params and omits null values', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['results' => []]),
    ]);

    $client->commerce()->paymentLinks()->list(['limit' => 10, 'after' => null]);

    parse_str($mock->getLastRequest()->getUri()->getQuery(), $query);
    expect($query['limit'])->toBe('10');
    expect($query)->not->toHaveKey('after');
});

it('get() sends GET /commerce/payment-links/2026-09/payment-links/{id}', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['id' => 'link-42', 'name' => 'Test Link']),
    ]);

    $result = $client->commerce()->paymentLinks()->get('link-42');

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('GET');
    expect($request->getUri()->getPath())->toBe('/commerce/payment-links/2026-09/payment-links/link-42');
    expect($result)->toBe(['id' => 'link-42', 'name' => 'Test Link']);
});

it('all() paginates across two pages via the Paginator', function () {
    [$client] = mockClient([
        jsonResponse(200, [
            'results' => [['id' => 'link-1'], ['id' => 'link-2']],
            'paging' => ['next' => ['after' => 'cursor-2']],
        ]),
        jsonResponse(200, [
            'results' => [['id' => 'link-3']],
        ]),
    ]);

    $items = $client->commerce()->paymentLinks()->all()->all();

    expect($items)->toBe([['id' => 'link-1'], ['id' => 'link-2'], ['id' => 'link-3']]);
});

it('version override changes the date segment in the path', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['results' => []]),
    ]);

    $client->commerce()->paymentLinks('2026-03')->list();

    expect($mock->getLastRequest()->getUri()->getPath())
        ->toBe('/commerce/payment-links/2026-03/payment-links');
});
