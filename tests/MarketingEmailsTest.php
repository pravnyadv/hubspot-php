<?php

declare(strict_types=1);

it('list() sends GET /marketing/emails/2026-09', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['results' => [['id' => 'email-1']]]),
    ]);

    $result = $client->marketing()->emails()->list();

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('GET');
    expect($request->getUri()->getPath())->toBe('/marketing/emails/2026-09');
    expect($result)->toBe(['results' => [['id' => 'email-1']]]);
});

it('list() passes query params and omits null values', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['results' => []]),
    ]);

    $client->marketing()->emails()->list(['limit' => 20, 'archived' => null]);

    parse_str($mock->getLastRequest()->getUri()->getQuery(), $query);
    expect($query['limit'])->toBe('20');
    expect($query)->not->toHaveKey('archived');
});

it('all() paginates across two pages', function () {
    [$client] = mockClient([
        jsonResponse(200, [
            'results' => [['id' => 'email-1'], ['id' => 'email-2']],
            'paging' => ['next' => ['after' => 'page2cursor']],
        ]),
        jsonResponse(200, [
            'results' => [['id' => 'email-3']],
        ]),
    ]);

    $items = $client->marketing()->emails()->all()->all();

    expect($items)->toBe([['id' => 'email-1'], ['id' => 'email-2'], ['id' => 'email-3']]);
});

it('get() sends GET /marketing/emails/2026-09/{emailId}', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['id' => 'email-1', 'name' => 'My Email']),
    ]);

    $result = $client->marketing()->emails()->get('email-1');

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('GET');
    expect($request->getUri()->getPath())->toBe('/marketing/emails/2026-09/email-1');
    expect($result)->toBe(['id' => 'email-1', 'name' => 'My Email']);
});

it('create() sends POST /marketing/emails/2026-09 with body', function () {
    [$client, $mock] = mockClient([
        jsonResponse(201, ['id' => 'email-new']),
    ]);

    $result = $client->marketing()->emails()->create(['name' => 'New Email', 'type' => 'BATCH_EMAIL']);

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('POST');
    expect($request->getUri()->getPath())->toBe('/marketing/emails/2026-09');
    expect(json_decode((string) $request->getBody(), true))->toBe(['name' => 'New Email', 'type' => 'BATCH_EMAIL']);
    expect($result)->toBe(['id' => 'email-new']);
});

it('update() sends PATCH /marketing/emails/2026-09/{emailId} with body', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['id' => 'email-1', 'name' => 'Updated']),
    ]);

    $result = $client->marketing()->emails()->update('email-1', ['name' => 'Updated']);

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('PATCH');
    expect($request->getUri()->getPath())->toBe('/marketing/emails/2026-09/email-1');
    expect(json_decode((string) $request->getBody(), true))->toBe(['name' => 'Updated']);
    expect($result)->toBe(['id' => 'email-1', 'name' => 'Updated']);
});

it('archive() sends DELETE /marketing/emails/2026-09/{emailId}', function () {
    [$client, $mock] = mockClient([
        jsonResponse(204),
    ]);

    $client->marketing()->emails()->archive('email-1');

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('DELETE');
    expect($request->getUri()->getPath())->toBe('/marketing/emails/2026-09/email-1');
});

it('clone_() sends POST /marketing/emails/2026-09/clone', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['id' => 'email-cloned']),
    ]);

    $result = $client->marketing()->emails()->clone_(['name' => 'Cloned Email', 'emailId' => 'email-1']);

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('POST');
    expect($request->getUri()->getPath())->toBe('/marketing/emails/2026-09/clone');
    expect($result)->toBe(['id' => 'email-cloned']);
});

it('publish() sends POST /marketing/emails/2026-09/{emailId}/publish', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['id' => 'email-1', 'state' => 'PUBLISHED']),
    ]);

    $result = $client->marketing()->emails()->publish('email-1');

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('POST');
    expect($request->getUri()->getPath())->toBe('/marketing/emails/2026-09/email-1/publish');
    expect($result)->toBe(['id' => 'email-1', 'state' => 'PUBLISHED']);
});

it('unpublish() sends POST /marketing/emails/2026-09/{emailId}/unpublish', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['id' => 'email-1', 'state' => 'DRAFT']),
    ]);

    $result = $client->marketing()->emails()->unpublish('email-1');

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('POST');
    expect($request->getUri()->getPath())->toBe('/marketing/emails/2026-09/email-1/unpublish');
    expect($result)->toBe(['id' => 'email-1', 'state' => 'DRAFT']);
});

it('getDraft() sends GET /marketing/emails/2026-09/{emailId}/draft', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['id' => 'email-1', 'isDraft' => true]),
    ]);

    $result = $client->marketing()->emails()->getDraft('email-1');

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('GET');
    expect($request->getUri()->getPath())->toBe('/marketing/emails/2026-09/email-1/draft');
    expect($result)->toBe(['id' => 'email-1', 'isDraft' => true]);
});

it('resetDraft() sends POST /marketing/emails/2026-09/{emailId}/draft/reset', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, []),
    ]);

    $client->marketing()->emails()->resetDraft('email-1');

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('POST');
    expect($request->getUri()->getPath())->toBe('/marketing/emails/2026-09/email-1/draft/reset');
});

it('revisions() sends GET /marketing/emails/2026-09/{emailId}/revisions', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['results' => [['id' => 'rev-1']]]),
    ]);

    $result = $client->marketing()->emails()->revisions('email-1');

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('GET');
    expect($request->getUri()->getPath())->toBe('/marketing/emails/2026-09/email-1/revisions');
    expect($result)->toBe(['results' => [['id' => 'rev-1']]]);
});

it('revision() sends GET /marketing/emails/2026-09/{emailId}/revisions/{revisionId}', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['id' => 'rev-1']),
    ]);

    $result = $client->marketing()->emails()->revision('email-1', 'rev-1');

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('GET');
    expect($request->getUri()->getPath())->toBe('/marketing/emails/2026-09/email-1/revisions/rev-1');
    expect($result)->toBe(['id' => 'rev-1']);
});

it('restoreRevision() sends POST /marketing/emails/2026-09/{emailId}/revisions/{revisionId}/restore', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, []),
    ]);

    $client->marketing()->emails()->restoreRevision('email-1', 'rev-1');

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('POST');
    expect($request->getUri()->getPath())->toBe('/marketing/emails/2026-09/email-1/revisions/rev-1/restore');
});

it('restoreRevisionToDraft() sends POST …/revisions/{revisionId}/restore-to-draft', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, []),
    ]);

    $client->marketing()->emails()->restoreRevisionToDraft('email-1', 'rev-1');

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('POST');
    expect($request->getUri()->getPath())->toBe('/marketing/emails/2026-09/email-1/revisions/rev-1/restore-to-draft');
});

it('statisticsList() sends GET /marketing/emails/2026-09/statistics/list', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['results' => []]),
    ]);

    $client->marketing()->emails()->statisticsList(['emailId' => 'email-1']);

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('GET');
    expect($request->getUri()->getPath())->toBe('/marketing/emails/2026-09/statistics/list');
    parse_str($request->getUri()->getQuery(), $query);
    expect($query['emailId'])->toBe('email-1');
});

it('statisticsHistogram() sends GET /marketing/emails/2026-09/statistics/histogram', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['results' => []]),
    ]);

    $client->marketing()->emails()->statisticsHistogram();

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('GET');
    expect($request->getUri()->getPath())->toBe('/marketing/emails/2026-09/statistics/histogram');
});

it('createAbVariation() sends POST /marketing/emails/2026-09/ab-test/create-variation', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['id' => 'variation-1']),
    ]);

    $result = $client->marketing()->emails()->createAbVariation(['emailId' => 'email-1']);

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('POST');
    expect($request->getUri()->getPath())->toBe('/marketing/emails/2026-09/ab-test/create-variation');
    expect($result)->toBe(['id' => 'variation-1']);
});

it('getAbVariation() sends GET /marketing/emails/2026-09/{emailId}/ab-test/get-variation', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['id' => 'variation-1']),
    ]);

    $result = $client->marketing()->emails()->getAbVariation('email-1');

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('GET');
    expect($request->getUri()->getPath())->toBe('/marketing/emails/2026-09/email-1/ab-test/get-variation');
    expect($result)->toBe(['id' => 'variation-1']);
});

it('version override changes the date segment in the path', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['results' => []]),
    ]);

    $client->marketing()->emails('2026-03')->list();

    expect($mock->getLastRequest()->getUri()->getPath())->toBe('/marketing/emails/2026-03');
});
