<?php

declare(strict_types=1);

it('search posts to the correct path with body params', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['lists' => [['listId' => 'abc']], 'total' => 1]),
    ]);

    $result = $client->crm()->lists()->search('newsletter', 50, ['hs_list_name']);

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('POST');
    expect($request->getUri()->getPath())->toBe('/crm/lists/2026-09/search');

    $body = json_decode((string) $request->getBody(), true);
    expect($body)->toBe([
        'query' => 'newsletter',
        'count' => 50,
        'additionalProperties' => ['hs_list_name'],
    ]);

    expect($result['lists'][0]['listId'])->toBe('abc');
});

it('search omits additionalProperties when empty', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['lists' => [], 'total' => 0]),
    ]);

    $client->crm()->lists()->search('test');

    $body = json_decode((string) $mock->getLastRequest()->getBody(), true);
    expect($body)->not->toHaveKey('additionalProperties');
    expect($body['query'])->toBe('test');
    expect($body['count'])->toBe(100);
});

it('get hits the correct path for a list id', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['listId' => '42', 'name' => 'VIPs']),
    ]);

    $result = $client->crm()->lists()->get('42');

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('GET');
    expect($request->getUri()->getPath())->toBe('/crm/lists/2026-09/42');
    expect($result['listId'])->toBe('42');
});

it('memberships hits the correct path and passes query params', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['results' => [['id' => '1']], 'paging' => []]),
    ]);

    $result = $client->crm()->lists()->memberships('99', 50, 'cursor-abc');

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('GET');
    expect($request->getUri()->getPath())->toBe('/crm/lists/2026-09/99/memberships');
    parse_str($request->getUri()->getQuery(), $query);
    expect($query['limit'])->toBe('50');
    expect($query['after'])->toBe('cursor-abc');
    expect($result['results'][0]['id'])->toBe('1');
});

it('memberships omits after when null', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['results' => []]),
    ]);

    $client->crm()->lists()->memberships('99');

    parse_str($mock->getLastRequest()->getUri()->getQuery(), $query);
    expect($query)->not->toHaveKey('after');
    expect($query['limit'])->toBe('100');
});

it('membershipsByJoinOrder hits the join-order path', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['results' => [['id' => '7']]]),
    ]);

    $result = $client->crm()->lists()->membershipsByJoinOrder('55', 100, 'next-cursor');

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('GET');
    expect($request->getUri()->getPath())->toBe('/crm/lists/2026-09/55/memberships/join-order');
    parse_str($request->getUri()->getQuery(), $query);
    expect($query['limit'])->toBe('100');
    expect($query['after'])->toBe('next-cursor');
    expect($result['results'][0]['id'])->toBe('7');
});

it('allMemberships walks two pages and concatenates all records', function () {
    [$client] = mockClient([
        jsonResponse(200, [
            'results' => [['id' => 'r1'], ['id' => 'r2']],
            'paging' => ['next' => ['after' => 'page2-cursor']],
        ]),
        jsonResponse(200, [
            'results' => [['id' => 'r3']],
        ]),
    ]);

    $records = $client->crm()->lists()->allMemberships('77')->all();

    expect($records)->toBe([
        ['id' => 'r1'],
        ['id' => 'r2'],
        ['id' => 'r3'],
    ]);
});

it('allMembershipsByJoinOrder walks two pages and concatenates all records', function () {
    [$client] = mockClient([
        jsonResponse(200, [
            'results' => [['id' => 'j1'], ['id' => 'j2']],
            'paging' => ['next' => ['after' => 'join-cursor']],
        ]),
        jsonResponse(200, [
            'results' => [['id' => 'j3']],
        ]),
    ]);

    $records = $client->crm()->lists()->allMembershipsByJoinOrder('88')->all();

    expect($records)->toBe([
        ['id' => 'j1'],
        ['id' => 'j2'],
        ['id' => 'j3'],
    ]);
});

it('per-resource version override changes the date segment in the path', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['listId' => '1']),
    ]);

    $client->crm()->lists('2026-03')->get('1');

    expect($mock->getLastRequest()->getUri()->getPath())->toBe('/crm/lists/2026-03/1');
});

it('create() POSTs a new list', function () {
    [$client, $mock] = mockClient([jsonResponse(201, ['listId' => '5'])]);

    $client->crm()->lists()->create(['name' => 'VIPs', 'objectTypeId' => '0-1', 'processingType' => 'MANUAL']);

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('POST');
    expect($request->getUri()->getPath())->toBe('/crm/lists/2026-09');
});

it('addMembers() and removeMembers() PUT a record-id array', function () {
    [$client, $mock] = mockClient([jsonResponse(200), jsonResponse(200)]);

    $client->crm()->lists()->addMembers('5', [1, 2, 3]);
    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('PUT');
    expect($request->getUri()->getPath())->toBe('/crm/lists/2026-09/5/memberships/add');
    expect(json_decode((string) $request->getBody(), true))->toBe([1, 2, 3]);

    $client->crm()->lists()->removeMembers('5', [1]);
    expect($mock->getLastRequest()->getUri()->getPath())->toBe('/crm/lists/2026-09/5/memberships/remove');
});

it('addAndRemoveMembers() PUTs the split body; removeAllMembers() DELETEs', function () {
    [$client, $mock] = mockClient([jsonResponse(200), jsonResponse(204)]);

    $client->crm()->lists()->addAndRemoveMembers('5', [1], [2]);
    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('PUT');
    expect($request->getUri()->getPath())->toBe('/crm/lists/2026-09/5/memberships/add-and-remove');
    expect(json_decode((string) $request->getBody(), true))->toBe(['recordIdsToAdd' => [1], 'recordIdsToRemove' => [2]]);

    $client->crm()->lists()->removeAllMembers('5');
    expect($mock->getLastRequest()->getMethod())->toBe('DELETE');
    expect($mock->getLastRequest()->getUri()->getPath())->toBe('/crm/lists/2026-09/5/memberships');
});

it('search sends the optional spec fields only when given', function () {
    [$client, $mock] = mockClient([jsonResponse(200, ['lists' => []])]);

    $client->crm()->lists()->search('vip', 50, offset: 50, sort: 'HS_CREATED_AT', objectTypeId: '0-1', processingTypes: ['MANUAL'], listIds: ['7']);

    expect(json_decode((string) $mock->getLastRequest()->getBody(), true))->toBe([
        'query' => 'vip',
        'count' => 50,
        'offset' => 50,
        'sort' => 'HS_CREATED_AT',
        'objectTypeId' => '0-1',
        'processingTypes' => ['MANUAL'],
        'listIds' => ['7'],
    ]);
});
