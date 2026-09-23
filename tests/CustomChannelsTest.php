<?php

declare(strict_types=1);

it('list() sends GET /conversations/custom-channels/2026-09', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['results' => [['id' => 'ch-1']]]),
    ]);

    $result = $client->conversations()->customChannels()->list();

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('GET');
    expect($request->getUri()->getPath())->toBe('/conversations/custom-channels/2026-09');
    expect($result)->toBe(['results' => [['id' => 'ch-1']]]);
});

it('list() passes query params', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['results' => []]),
    ]);

    $client->conversations()->customChannels()->list(['limit' => 10, 'after' => 'cur']);

    parse_str($mock->getLastRequest()->getUri()->getQuery(), $q);
    expect($q['limit'])->toBe('10');
    expect($q['after'])->toBe('cur');
});

it('list() omits null query params', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['results' => []]),
    ]);

    $client->conversations()->customChannels()->list(['after' => null]);

    parse_str($mock->getLastRequest()->getUri()->getQuery(), $q);
    expect($q)->not->toHaveKey('after');
});

it('all() paginates via Paginator', function () {
    [$client] = mockClient([
        jsonResponse(200, [
            'results' => [['id' => 'ch-1']],
            'paging' => ['next' => ['after' => 'p2']],
        ]),
        jsonResponse(200, ['results' => [['id' => 'ch-2']]]),
    ]);

    $items = $client->conversations()->customChannels()->all()->all();

    expect($items)->toBe([['id' => 'ch-1'], ['id' => 'ch-2']]);
});

it('get() sends GET /conversations/custom-channels/2026-09/{channelId}', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['id' => 'ch-1', 'name' => 'My Channel']),
    ]);

    $result = $client->conversations()->customChannels()->get('ch-1');

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('GET');
    expect($request->getUri()->getPath())->toBe('/conversations/custom-channels/2026-09/ch-1');
    expect($result)->toBe(['id' => 'ch-1', 'name' => 'My Channel']);
});

it('create() sends POST /conversations/custom-channels/2026-09 with body', function () {
    [$client, $mock] = mockClient([
        jsonResponse(201, ['id' => 'ch-new']),
    ]);

    $result = $client->conversations()->customChannels()->create(['name' => 'Bot Channel']);

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('POST');
    expect($request->getUri()->getPath())->toBe('/conversations/custom-channels/2026-09');
    expect(json_decode((string) $request->getBody(), true))->toBe(['name' => 'Bot Channel']);
    expect($result)->toBe(['id' => 'ch-new']);
});

it('update() sends PATCH /conversations/custom-channels/2026-09/{channelId}', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['id' => 'ch-1', 'name' => 'Updated']),
    ]);

    $result = $client->conversations()->customChannels()->update('ch-1', ['name' => 'Updated']);

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('PATCH');
    expect($request->getUri()->getPath())->toBe('/conversations/custom-channels/2026-09/ch-1');
    expect(json_decode((string) $request->getBody(), true))->toBe(['name' => 'Updated']);
    expect($result)->toBe(['id' => 'ch-1', 'name' => 'Updated']);
});

it('archive() sends DELETE /conversations/custom-channels/2026-09/{channelId}', function () {
    [$client, $mock] = mockClient([
        jsonResponse(204, ''),
    ]);

    $client->conversations()->customChannels()->archive('ch-1');

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('DELETE');
    expect($request->getUri()->getPath())->toBe('/conversations/custom-channels/2026-09/ch-1');
});

it('listAccounts() sends GET .../channel-accounts', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['results' => [['id' => 'acc-1']]]),
    ]);

    $result = $client->conversations()->customChannels()->listAccounts('ch-1');

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('GET');
    expect($request->getUri()->getPath())->toBe('/conversations/custom-channels/2026-09/ch-1/channel-accounts');
    expect($result)->toBe(['results' => [['id' => 'acc-1']]]);
});

it('getAccount() sends GET .../channel-accounts/{accountId}', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['id' => 'acc-1']),
    ]);

    $result = $client->conversations()->customChannels()->getAccount('ch-1', 'acc-1');

    expect($mock->getLastRequest()->getUri()->getPath())
        ->toBe('/conversations/custom-channels/2026-09/ch-1/channel-accounts/acc-1');
    expect($result)->toBe(['id' => 'acc-1']);
});

it('createAccount() sends POST .../channel-accounts', function () {
    [$client, $mock] = mockClient([
        jsonResponse(201, ['id' => 'acc-new']),
    ]);

    $result = $client->conversations()->customChannels()->createAccount('ch-1', ['label' => 'Main']);

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('POST');
    expect($request->getUri()->getPath())->toBe('/conversations/custom-channels/2026-09/ch-1/channel-accounts');
    expect(json_decode((string) $request->getBody(), true))->toBe(['label' => 'Main']);
    expect($result)->toBe(['id' => 'acc-new']);
});

it('updateAccount() sends PATCH .../channel-accounts/{accountId}', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['id' => 'acc-1']),
    ]);

    $client->conversations()->customChannels()->updateAccount('ch-1', 'acc-1', ['label' => 'Updated']);

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('PATCH');
    expect($request->getUri()->getPath())
        ->toBe('/conversations/custom-channels/2026-09/ch-1/channel-accounts/acc-1');
});

it('updateStagingToken() sends PATCH .../channel-account-staging-tokens/{token} with a body', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['token' => 'stg-tok']),
    ]);

    $result = $client->conversations()->customChannels()
        ->updateStagingToken('ch-1', 'my-account-token', ['accountName' => 'Support']);

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('PATCH');
    expect($request->getUri()->getPath())
        ->toBe('/conversations/custom-channels/2026-09/ch-1/channel-account-staging-tokens/my-account-token');
    expect(json_decode((string) $request->getBody(), true))->toBe(['accountName' => 'Support']);
    expect($result)->toBe(['token' => 'stg-tok']);
});

it('updateMessage() sends PATCH .../messages/{messageId} with a status body', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['id' => 'msg-1', 'statusType' => 'FAILED']),
    ]);

    $result = $client->conversations()->customChannels()
        ->updateMessage('ch-1', 'msg-1', ['statusType' => 'FAILED', 'errorMessage' => 'nope']);

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('PATCH');
    expect($request->getUri()->getPath())->toBe('/conversations/custom-channels/2026-09/ch-1/messages/msg-1');
    expect(json_decode((string) $request->getBody(), true))->toBe(['statusType' => 'FAILED', 'errorMessage' => 'nope']);
    expect($result)->toBe(['id' => 'msg-1', 'statusType' => 'FAILED']);
});

it('getMessage() sends GET .../messages/{messageId}', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['id' => 'msg-1', 'text' => 'Hello']),
    ]);

    $result = $client->conversations()->customChannels()->getMessage('ch-1', 'msg-1');

    expect($mock->getLastRequest()->getUri()->getPath())
        ->toBe('/conversations/custom-channels/2026-09/ch-1/messages/msg-1');
    expect($result)->toBe(['id' => 'msg-1', 'text' => 'Hello']);
});

it('sendMessage() sends POST .../messages with body', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['id' => 'msg-new']),
    ]);

    $result = $client->conversations()->customChannels()->sendMessage('ch-1', ['text' => 'Hi there']);

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('POST');
    expect($request->getUri()->getPath())->toBe('/conversations/custom-channels/2026-09/ch-1/messages');
    expect(json_decode((string) $request->getBody(), true))->toBe(['text' => 'Hi there']);
    expect($result)->toBe(['id' => 'msg-new']);
});

it('version override changes the date segment in the path', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['results' => []]),
    ]);

    $client->conversations()->customChannels('2026-03')->list();

    expect($mock->getLastRequest()->getUri()->getPath())
        ->toBe('/conversations/custom-channels/2026-03');
});
