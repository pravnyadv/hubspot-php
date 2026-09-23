<?php

declare(strict_types=1);

it('list() sends GET /commerce/price-books/2026-09/price-books', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['results' => [['id' => 'pb-1']]]),
    ]);

    $result = $client->commerce()->priceBooks()->list();

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('GET');
    expect($request->getUri()->getPath())->toBe('/commerce/price-books/2026-09/price-books');
    expect($result)->toBe(['results' => [['id' => 'pb-1']]]);
});

it('list() passes query params and omits null values', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['results' => []]),
    ]);

    $client->commerce()->priceBooks()->list(['limit' => 20, 'after' => null]);

    parse_str($mock->getLastRequest()->getUri()->getQuery(), $query);
    expect($query['limit'])->toBe('20');
    expect($query)->not->toHaveKey('after');
});

it('all() paginates across two pages via the Paginator', function () {
    [$client] = mockClient([
        jsonResponse(200, [
            'results' => [['id' => 'pb-1'], ['id' => 'pb-2']],
            'paging' => ['next' => ['after' => 'cursor-2']],
        ]),
        jsonResponse(200, [
            'results' => [['id' => 'pb-3']],
        ]),
    ]);

    $items = $client->commerce()->priceBooks()->all()->all();

    expect($items)->toBe([['id' => 'pb-1'], ['id' => 'pb-2'], ['id' => 'pb-3']]);
});

it('get() sends GET /commerce/price-books/2026-09/price-books/{id}', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['id' => 'pb-99', 'name' => 'VIP Prices']),
    ]);

    $result = $client->commerce()->priceBooks()->get('pb-99');

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('GET');
    expect($request->getUri()->getPath())->toBe('/commerce/price-books/2026-09/price-books/pb-99');
    expect($result)->toBe(['id' => 'pb-99', 'name' => 'VIP Prices']);
});

it('create() sends POST with JSON body', function () {
    [$client, $mock] = mockClient([
        jsonResponse(201, ['id' => 'pb-new', 'name' => 'Partner Prices']),
    ]);

    $result = $client->commerce()->priceBooks()->create(['name' => 'Partner Prices']);

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('POST');
    expect($request->getUri()->getPath())->toBe('/commerce/price-books/2026-09/price-books');
    expect(json_decode((string) $request->getBody(), true))->toBe(['name' => 'Partner Prices']);
    expect($result)->toBe(['id' => 'pb-new', 'name' => 'Partner Prices']);
});

it('update() sends PATCH with JSON body', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['id' => 'pb-1', 'name' => 'Updated']),
    ]);

    $result = $client->commerce()->priceBooks()->update('pb-1', ['name' => 'Updated']);

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('PATCH');
    expect($request->getUri()->getPath())->toBe('/commerce/price-books/2026-09/price-books/pb-1');
    expect(json_decode((string) $request->getBody(), true))->toBe(['name' => 'Updated']);
    expect($result)->toBe(['id' => 'pb-1', 'name' => 'Updated']);
});

it('archive() sends DELETE and returns void', function () {
    [$client, $mock] = mockClient([
        jsonResponse(204),
    ]);

    $client->commerce()->priceBooks()->archive('pb-1');

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('DELETE');
    expect($request->getUri()->getPath())->toBe('/commerce/price-books/2026-09/price-books/pb-1');
});

it('activate() sends POST to /activate', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['id' => 'pb-1', 'active' => true]),
    ]);

    $result = $client->commerce()->priceBooks()->activate('pb-1');

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('POST');
    expect($request->getUri()->getPath())->toBe('/commerce/price-books/2026-09/price-books/pb-1/activate');
    expect($result)->toBe(['id' => 'pb-1', 'active' => true]);
});

it('deactivate() sends POST to /deactivate', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['id' => 'pb-1', 'active' => false]),
    ]);

    $result = $client->commerce()->priceBooks()->deactivate('pb-1');

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('POST');
    expect($request->getUri()->getPath())->toBe('/commerce/price-books/2026-09/price-books/pb-1/deactivate');
    expect($result)->toBe(['id' => 'pb-1', 'active' => false]);
});

it('validate() sends POST to /validate with body', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['valid' => true]),
    ]);

    $result = $client->commerce()->priceBooks()->validate('pb-1', ['currency' => 'USD']);

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('POST');
    expect($request->getUri()->getPath())->toBe('/commerce/price-books/2026-09/price-books/pb-1/validate');
    expect(json_decode((string) $request->getBody(), true))->toBe(['currency' => 'USD']);
    expect($result)->toBe(['valid' => true]);
});

it('listItems() sends GET to /items', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['results' => [['id' => 'item-1']]]),
    ]);

    $result = $client->commerce()->priceBooks()->listItems('pb-1');

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('GET');
    expect($request->getUri()->getPath())->toBe('/commerce/price-books/2026-09/price-books/pb-1/items');
    expect($result)->toBe(['results' => [['id' => 'item-1']]]);
});

it('allItems() paginates items across two pages', function () {
    [$client] = mockClient([
        jsonResponse(200, [
            'results' => [['id' => 'item-1']],
            'paging' => ['next' => ['after' => 'cur2']],
        ]),
        jsonResponse(200, [
            'results' => [['id' => 'item-2']],
        ]),
    ]);

    $items = $client->commerce()->priceBooks()->allItems('pb-1')->all();

    expect($items)->toBe([['id' => 'item-1'], ['id' => 'item-2']]);
});

it('createItem() sends POST to /items with JSON body', function () {
    [$client, $mock] = mockClient([
        jsonResponse(201, ['id' => 'item-new']),
    ]);

    $result = $client->commerce()->priceBooks()->createItem('pb-1', ['productId' => 'prod-1', 'price' => 9.99]);

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('POST');
    expect($request->getUri()->getPath())->toBe('/commerce/price-books/2026-09/price-books/pb-1/items');
    expect(json_decode((string) $request->getBody(), true))->toBe(['productId' => 'prod-1', 'price' => 9.99]);
    expect($result)->toBe(['id' => 'item-new']);
});

it('getItem() sends GET to /items/{itemId}', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['id' => 'item-7', 'price' => 19.99]),
    ]);

    $result = $client->commerce()->priceBooks()->getItem('pb-1', 'item-7');

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('GET');
    expect($request->getUri()->getPath())->toBe('/commerce/price-books/2026-09/price-books/pb-1/items/item-7');
    expect($result)->toBe(['id' => 'item-7', 'price' => 19.99]);
});

it('updateItem() sends PATCH to /items/{itemId}', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['id' => 'item-7', 'price' => 24.99]),
    ]);

    $result = $client->commerce()->priceBooks()->updateItem('pb-1', 'item-7', ['price' => 24.99]);

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('PATCH');
    expect($request->getUri()->getPath())->toBe('/commerce/price-books/2026-09/price-books/pb-1/items/item-7');
    expect(json_decode((string) $request->getBody(), true))->toBe(['price' => 24.99]);
    expect($result)->toBe(['id' => 'item-7', 'price' => 24.99]);
});

it('archiveItem() sends DELETE to /items/{itemId} and returns void', function () {
    [$client, $mock] = mockClient([
        jsonResponse(204),
    ]);

    $client->commerce()->priceBooks()->archiveItem('pb-1', 'item-7');

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('DELETE');
    expect($request->getUri()->getPath())->toBe('/commerce/price-books/2026-09/price-books/pb-1/items/item-7');
});

it('batchCreateItems() sends POST to /items/batch/create with inputs', function () {
    [$client, $mock] = mockClient([
        jsonResponse(201, ['results' => [['id' => 'item-a'], ['id' => 'item-b']]]),
    ]);

    $inputs = [['productId' => 'prod-1', 'price' => 5], ['productId' => 'prod-2', 'price' => 10]];
    $result = $client->commerce()->priceBooks()->batchCreateItems('pb-1', $inputs);

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('POST');
    expect($request->getUri()->getPath())->toBe('/commerce/price-books/2026-09/price-books/pb-1/items/batch/create');
    expect(json_decode((string) $request->getBody(), true))->toBe(['inputs' => $inputs]);
    expect($result)->toBe(['results' => [['id' => 'item-a'], ['id' => 'item-b']]]);
});

it('batchUpdateItems() sends POST to /items/batch/update with inputs', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['results' => [['id' => 'item-a']]]),
    ]);

    $inputs = [['id' => 'item-a', 'price' => 7.50]];
    $result = $client->commerce()->priceBooks()->batchUpdateItems('pb-1', $inputs);

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('POST');
    expect($request->getUri()->getPath())->toBe('/commerce/price-books/2026-09/price-books/pb-1/items/batch/update');
    expect(json_decode((string) $request->getBody(), true))->toBe(['inputs' => $inputs]);
    expect($result)->toBe(['results' => [['id' => 'item-a']]]);
});

it('batchArchiveItems() sends POST to /items/batch/archive with inputs', function () {
    [$client, $mock] = mockClient([
        jsonResponse(204),
    ]);

    $inputs = [['id' => 'item-a'], ['id' => 'item-b']];
    $client->commerce()->priceBooks()->batchArchiveItems('pb-1', $inputs);

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('POST');
    expect($request->getUri()->getPath())->toBe('/commerce/price-books/2026-09/price-books/pb-1/items/batch/archive');
    expect(json_decode((string) $request->getBody(), true))->toBe(['inputs' => $inputs]);
});

it('version override changes the date segment in the path', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['results' => []]),
    ]);

    $client->commerce()->priceBooks('2026-03')->list();

    expect($mock->getLastRequest()->getUri()->getPath())
        ->toBe('/commerce/price-books/2026-03/price-books');
});
