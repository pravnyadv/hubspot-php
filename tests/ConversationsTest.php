<?php

declare(strict_types=1);

it('threads() sends GET /conversations/conversations/2026-09/threads', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['results' => [['id' => 'thread-1']]]),
    ]);

    $result = $client->conversations()->inbox()->threads();

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('GET');
    expect($request->getUri()->getPath())->toBe('/conversations/conversations/2026-09/threads');
    expect($result)->toBe(['results' => [['id' => 'thread-1']]]);
});

it('threads() passes query params and omits null values', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['results' => []]),
    ]);

    $client->conversations()->inbox()->threads(['limit' => 50, 'after' => 'cursor-abc', 'status' => null]);

    parse_str($mock->getLastRequest()->getUri()->getQuery(), $query);
    expect($query['limit'])->toBe('50');
    expect($query['after'])->toBe('cursor-abc');
    expect($query)->not->toHaveKey('status');
});

it('thread() sends GET /conversations/conversations/2026-09/threads/{threadId}', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['id' => 'thread-42', 'status' => 'OPEN']),
    ]);

    $result = $client->conversations()->inbox()->thread('thread-42');

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('GET');
    expect($request->getUri()->getPath())->toBe('/conversations/conversations/2026-09/threads/thread-42');
    expect($result)->toBe(['id' => 'thread-42', 'status' => 'OPEN']);
});

it('messages() sends GET /conversations/conversations/2026-09/threads/{threadId}/messages', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['results' => [['id' => 'msg-1']]]),
    ]);

    $result = $client->conversations()->inbox()->messages('thread-42');

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('GET');
    expect($request->getUri()->getPath())->toBe('/conversations/conversations/2026-09/threads/thread-42/messages');
    expect($result)->toBe(['results' => [['id' => 'msg-1']]]);
});

it('messages() passes query params and omits null values', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['results' => []]),
    ]);

    $client->conversations()->inbox()->messages('thread-42', ['limit' => 25, 'after' => null]);

    parse_str($mock->getLastRequest()->getUri()->getQuery(), $query);
    expect($query['limit'])->toBe('25');
    expect($query)->not->toHaveKey('after');
});

it('sendMessage() sends POST to threads/{threadId}/messages with JSON body', function () {
    [$client, $mock] = mockClient([
        jsonResponse(201, ['id' => 'msg-99', 'type' => 'MESSAGE']),
    ]);

    $payload = ['type' => 'MESSAGE', 'text' => 'Hello there'];
    $result = $client->conversations()->inbox()->sendMessage('thread-42', $payload);

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('POST');
    expect($request->getUri()->getPath())->toBe('/conversations/conversations/2026-09/threads/thread-42/messages');
    $body = json_decode((string) $request->getBody(), true);
    expect($body['type'])->toBe('MESSAGE');
    expect($body['text'])->toBe('Hello there');
    expect($result)->toBe(['id' => 'msg-99', 'type' => 'MESSAGE']);
});

it('inboxes() sends GET /conversations/conversations/2026-09/inboxes', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['results' => [['id' => 'inbox-1', 'name' => 'Support']]]),
    ]);

    $result = $client->conversations()->inbox()->inboxes();

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('GET');
    expect($request->getUri()->getPath())->toBe('/conversations/conversations/2026-09/inboxes');
    expect($result)->toBe(['results' => [['id' => 'inbox-1', 'name' => 'Support']]]);
});

it('channels() sends GET /conversations/conversations/2026-09/channels', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['results' => [['id' => 'ch-1', 'type' => 'EMAIL']]]),
    ]);

    $result = $client->conversations()->inbox()->channels();

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('GET');
    expect($request->getUri()->getPath())->toBe('/conversations/conversations/2026-09/channels');
    expect($result)->toBe(['results' => [['id' => 'ch-1', 'type' => 'EMAIL']]]);
});

it('channelAccounts() sends GET /conversations/conversations/2026-09/channel-accounts', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['results' => [['id' => 'ca-1', 'channelId' => 'ch-1']]]),
    ]);

    $result = $client->conversations()->inbox()->channelAccounts();

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('GET');
    expect($request->getUri()->getPath())->toBe('/conversations/conversations/2026-09/channel-accounts');
    expect($result)->toBe(['results' => [['id' => 'ca-1', 'channelId' => 'ch-1']]]);
});

it('version override changes the date segment in all paths', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['results' => []]),
        jsonResponse(200, []),
        jsonResponse(200, ['results' => []]),
    ]);

    $conv = $client->conversations()->inbox('2026-03');

    $conv->threads();
    expect($mock->getLastRequest()->getUri()->getPath())->toBe('/conversations/conversations/2026-03/threads');

    $conv->inboxes();
    expect($mock->getLastRequest()->getUri()->getPath())->toBe('/conversations/conversations/2026-03/inboxes');

    $conv->channelAccounts();
    expect($mock->getLastRequest()->getUri()->getPath())->toBe('/conversations/conversations/2026-03/channel-accounts');
});

it('getInbox() GETs a single inbox by id', function () {
    [$client, $mock] = mockClient([jsonResponse(200, ['id' => 'inb-1', 'type' => 'SUPPORT'])]);

    $client->conversations()->inbox()->getInbox('inb-1');

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('GET');
    expect($request->getUri()->getPath())->toBe('/conversations/conversations/2026-09/inboxes/inb-1');
});

it('thread() forwards a query param such as association', function () {
    [$client, $mock] = mockClient([jsonResponse(200, [])]);

    $client->conversations()->inbox()->thread('t-1', ['association' => 'TICKET']);

    $request = $mock->getLastRequest();
    parse_str($request->getUri()->getQuery(), $q);
    expect($q['association'])->toBe('TICKET');
    expect($request->getUri()->getPath())->toBe('/conversations/conversations/2026-09/threads/t-1');
});
