<?php

declare(strict_types=1);

it('list() sends GET /automation/sequences/2026-09', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['results' => [['id' => 'seq-1']]]),
    ]);

    $result = $client->automation()->sequences()->list();

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('GET');
    expect($request->getUri()->getPath())->toBe('/automation/sequences/2026-09');
    expect($result)->toBe(['results' => [['id' => 'seq-1']]]);
});

it('list() passes query params and omits null values', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['results' => []]),
    ]);

    $client->automation()->sequences()->list(['limit' => 20, 'after' => null]);

    parse_str($mock->getLastRequest()->getUri()->getQuery(), $query);
    expect($query['limit'])->toBe('20');
    expect($query)->not->toHaveKey('after');
});

it('all() paginates across two pages via the Paginator', function () {
    [$client] = mockClient([
        jsonResponse(200, [
            'results' => [['id' => 'seq-1'], ['id' => 'seq-2']],
            'paging' => ['next' => ['after' => 'page2cursor']],
        ]),
        jsonResponse(200, [
            'results' => [['id' => 'seq-3']],
        ]),
    ]);

    $items = $client->automation()->sequences()->all()->all();

    expect($items)->toBe([['id' => 'seq-1'], ['id' => 'seq-2'], ['id' => 'seq-3']]);
});

it('get() sends GET /automation/sequences/2026-09/{sequenceId}', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['id' => 'seq-1', 'name' => 'Outreach']),
    ]);

    $result = $client->automation()->sequences()->get('seq-1');

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('GET');
    expect($request->getUri()->getPath())->toBe('/automation/sequences/2026-09/seq-1');
    expect($result)->toBe(['id' => 'seq-1', 'name' => 'Outreach']);
});

it('enroll() sends POST /automation/sequences/2026-09/enrollments', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['enrollmentId' => 'enr-new']),
    ]);

    $result = $client->automation()->sequences()->enroll(['sequenceId' => 'seq-1', 'contactId' => 'contact-42']);

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('POST');
    expect($request->getUri()->getPath())->toBe('/automation/sequences/2026-09/enrollments');
    $body = json_decode((string) $request->getBody(), true);
    expect($body['sequenceId'])->toBe('seq-1');
    expect($body['contactId'])->toBe('contact-42');
    expect($result)->toBe(['enrollmentId' => 'enr-new']);
});

it('contactEnrollments() sends GET /automation/sequences/2026-09/enrollments/contact/{contactId}', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['results' => [['enrollmentId' => 'enr-1']]]),
    ]);

    $result = $client->automation()->sequences()->contactEnrollments('contact-42');

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('GET');
    expect($request->getUri()->getPath())->toBe('/automation/sequences/2026-09/enrollments/contact/contact-42');
    expect($result)->toBe(['results' => [['enrollmentId' => 'enr-1']]]);
});

it('serviceAccountSequences() sends GET /automation/sequences/2026-09/serviceaccounts/sequences', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['results' => [['id' => 'seq-sa-1']]]),
    ]);

    $result = $client->automation()->sequences()->serviceAccountSequences();

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('GET');
    expect($request->getUri()->getPath())->toBe('/automation/sequences/2026-09/serviceaccounts/sequences');
    expect($result)->toBe(['results' => [['id' => 'seq-sa-1']]]);
});

it('serviceAccountSequence() sends GET /automation/sequences/2026-09/serviceaccounts/sequences/{sequenceId}', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['id' => 'seq-sa-1', 'name' => 'SA Outreach']),
    ]);

    $result = $client->automation()->sequences()->serviceAccountSequence('seq-sa-1');

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('GET');
    expect($request->getUri()->getPath())->toBe('/automation/sequences/2026-09/serviceaccounts/sequences/seq-sa-1');
    expect($result)->toBe(['id' => 'seq-sa-1', 'name' => 'SA Outreach']);
});

it('performance() sends GET /automation/sequences/2026-09/serviceaccounts/sequences/{sequenceId}/performance', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['enrollments' => 42, 'completions' => 30]),
    ]);

    $result = $client->automation()->sequences()->performance('seq-sa-1');

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('GET');
    expect($request->getUri()->getPath())->toBe('/automation/sequences/2026-09/serviceaccounts/sequences/seq-sa-1/performance');
    expect($result)->toBe(['enrollments' => 42, 'completions' => 30]);
});

it('version override changes the date segment in the path', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['results' => []]),
    ]);

    $client->automation()->sequences('2026-03')->list();

    expect($mock->getLastRequest()->getUri()->getPath())->toBe('/automation/sequences/2026-03');
});
