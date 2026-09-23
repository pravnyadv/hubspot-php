<?php

declare(strict_types=1);

it('list() sends GET /marketing/campaigns/2026-09', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['results' => [['id' => 'camp-1']]]),
    ]);

    $result = $client->marketing()->campaigns()->list();

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('GET');
    expect($request->getUri()->getPath())->toBe('/marketing/campaigns/2026-09');
    expect($result)->toBe(['results' => [['id' => 'camp-1']]]);
});

it('list() passes query params and omits nulls', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['results' => []]),
    ]);

    $client->marketing()->campaigns()->list(['limit' => 10, 'after' => null]);

    parse_str($mock->getLastRequest()->getUri()->getQuery(), $query);
    expect($query['limit'])->toBe('10');
    expect($query)->not->toHaveKey('after');
});

it('all() paginates across two pages via the Paginator', function () {
    [$client] = mockClient([
        jsonResponse(200, [
            'results' => [['id' => 'camp-1'], ['id' => 'camp-2']],
            'paging' => ['next' => ['after' => 'cursor2']],
        ]),
        jsonResponse(200, [
            'results' => [['id' => 'camp-3']],
        ]),
    ]);

    $items = $client->marketing()->campaigns()->all()->all();

    expect($items)->toBe([['id' => 'camp-1'], ['id' => 'camp-2'], ['id' => 'camp-3']]);
});

it('get() sends GET /marketing/campaigns/2026-09/{campaignGuid}', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['id' => 'camp-abc', 'name' => 'Q4 Campaign']),
    ]);

    $result = $client->marketing()->campaigns()->get('camp-abc');

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('GET');
    expect($request->getUri()->getPath())->toBe('/marketing/campaigns/2026-09/camp-abc');
    expect($result)->toBe(['id' => 'camp-abc', 'name' => 'Q4 Campaign']);
});

it('batchCreate() sends POST /marketing/campaigns/2026-09/batch/create with inputs wrapper', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['status' => 'COMPLETE', 'results' => []]),
    ]);

    $inputs = [['name' => 'Campaign A']];
    $client->marketing()->campaigns()->batchCreate($inputs);

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('POST');
    expect($request->getUri()->getPath())->toBe('/marketing/campaigns/2026-09/batch/create');
    expect(json_decode((string) $request->getBody(), true))->toBe(['inputs' => $inputs]);
});

it('batchRead() sends POST /marketing/campaigns/2026-09/batch/read', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['status' => 'COMPLETE', 'results' => []]),
    ]);

    $inputs = [['id' => 'camp-1']];
    $client->marketing()->campaigns()->batchRead($inputs);

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('POST');
    expect($request->getUri()->getPath())->toBe('/marketing/campaigns/2026-09/batch/read');
    expect(json_decode((string) $request->getBody(), true))->toBe(['inputs' => $inputs]);
});

it('batchUpdate() sends POST /marketing/campaigns/2026-09/batch/update', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['status' => 'COMPLETE', 'results' => []]),
    ]);

    $inputs = [['id' => 'camp-1', 'name' => 'Updated']];
    $client->marketing()->campaigns()->batchUpdate($inputs);

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('POST');
    expect($request->getUri()->getPath())->toBe('/marketing/campaigns/2026-09/batch/update');
    expect(json_decode((string) $request->getBody(), true))->toBe(['inputs' => $inputs]);
});

it('batchArchive() sends POST /marketing/campaigns/2026-09/batch/archive', function () {
    [$client, $mock] = mockClient([
        jsonResponse(204, ''),
    ]);

    $client->marketing()->campaigns()->batchArchive([['id' => 'camp-1']]);

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('POST');
    expect($request->getUri()->getPath())->toBe('/marketing/campaigns/2026-09/batch/archive');
});

it('duplicate() sends POST /marketing/campaigns/2026-09/clone', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['cloneId' => 'clone-xyz']),
    ]);

    $result = $client->marketing()->campaigns()->duplicate(['campaignGuid' => 'camp-abc']);

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('POST');
    expect($request->getUri()->getPath())->toBe('/marketing/campaigns/2026-09/clone');
    expect(json_decode((string) $request->getBody(), true))->toBe(['campaignGuid' => 'camp-abc']);
    expect($result)->toBe(['cloneId' => 'clone-xyz']);
});

it('cloneStatus() sends GET /marketing/campaigns/2026-09/clone/{campaignGuid}/status', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['status' => 'COMPLETE']),
    ]);

    $result = $client->marketing()->campaigns()->cloneStatus('camp-abc');

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('GET');
    expect($request->getUri()->getPath())->toBe('/marketing/campaigns/2026-09/clone/camp-abc/status');
    expect($result)->toBe(['status' => 'COMPLETE']);
});

it('assetTypes() sends GET /marketing/campaigns/2026-09/asset-types', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['results' => ['EMAIL', 'LANDING_PAGE']]),
    ]);

    $result = $client->marketing()->campaigns()->assetTypes();

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('GET');
    expect($request->getUri()->getPath())->toBe('/marketing/campaigns/2026-09/asset-types');
    expect($result)->toBe(['results' => ['EMAIL', 'LANDING_PAGE']]);
});

it('assets() sends GET /marketing/campaigns/2026-09/{campaignGuid}/assets/{assetType}', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['results' => [['id' => 'email-1']]]),
    ]);

    $result = $client->marketing()->campaigns()->assets('camp-abc', 'EMAIL');

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('GET');
    expect($request->getUri()->getPath())->toBe('/marketing/campaigns/2026-09/camp-abc/assets/EMAIL');
    expect($result)->toBe(['results' => [['id' => 'email-1']]]);
});

it('addAsset() sends PUT /marketing/campaigns/2026-09/{campaignGuid}/assets/{assetType}/{assetId}', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, []),
    ]);

    $client->marketing()->campaigns()->addAsset('camp-abc', 'EMAIL', 'email-1');

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('PUT');
    expect($request->getUri()->getPath())->toBe('/marketing/campaigns/2026-09/camp-abc/assets/EMAIL/email-1');
});

it('removeAsset() sends DELETE /marketing/campaigns/2026-09/{campaignGuid}/assets/{assetType}/{assetId}', function () {
    [$client, $mock] = mockClient([
        jsonResponse(204, ''),
    ]);

    $client->marketing()->campaigns()->removeAsset('camp-abc', 'EMAIL', 'email-1');

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('DELETE');
    expect($request->getUri()->getPath())->toBe('/marketing/campaigns/2026-09/camp-abc/assets/EMAIL/email-1');
});

it('budget() sends GET /marketing/campaigns/2026-09/{campaignGuid}/budget/{budgetId}', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['id' => 'bud-1', 'amount' => 5000]),
    ]);

    $result = $client->marketing()->campaigns()->budget('camp-abc', 'bud-1');

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('GET');
    expect($request->getUri()->getPath())->toBe('/marketing/campaigns/2026-09/camp-abc/budget/bud-1');
    expect($result)->toBe(['id' => 'bud-1', 'amount' => 5000]);
});

it('budgetTotals() sends GET /marketing/campaigns/2026-09/{campaignGuid}/budget/totals', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['total' => 5000, 'spent' => 2000]),
    ]);

    $result = $client->marketing()->campaigns()->budgetTotals('camp-abc');

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('GET');
    expect($request->getUri()->getPath())->toBe('/marketing/campaigns/2026-09/camp-abc/budget/totals');
    expect($result)->toBe(['total' => 5000, 'spent' => 2000]);
});

it('updateBudget() sends PUT /marketing/campaigns/2026-09/{campaignGuid}/budget/{budgetId}', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['id' => 'bud-1', 'amount' => 6000]),
    ]);

    $result = $client->marketing()->campaigns()->updateBudget('camp-abc', 'bud-1', ['amount' => 6000]);

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('PUT');
    expect($request->getUri()->getPath())->toBe('/marketing/campaigns/2026-09/camp-abc/budget/bud-1');
    expect(json_decode((string) $request->getBody(), true))->toBe(['amount' => 6000]);
    expect($result)->toBe(['id' => 'bud-1', 'amount' => 6000]);
});

it('deleteBudget() sends DELETE /marketing/campaigns/2026-09/{campaignGuid}/budget/{budgetId}', function () {
    [$client, $mock] = mockClient([
        jsonResponse(204, ''),
    ]);

    $client->marketing()->campaigns()->deleteBudget('camp-abc', 'bud-1');

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('DELETE');
    expect($request->getUri()->getPath())->toBe('/marketing/campaigns/2026-09/camp-abc/budget/bud-1');
});

it('metrics() sends GET /marketing/campaigns/2026-09/{campaignGuid}/reports/metrics', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['clicks' => 100, 'opens' => 200]),
    ]);

    $result = $client->marketing()->campaigns()->metrics('camp-abc');

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('GET');
    expect($request->getUri()->getPath())->toBe('/marketing/campaigns/2026-09/camp-abc/reports/metrics');
    expect($result)->toBe(['clicks' => 100, 'opens' => 200]);
});

it('contactReport() sends GET /marketing/campaigns/2026-09/{campaignGuid}/reports/contacts/{contactType}', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['results' => []]),
    ]);

    $client->marketing()->campaigns()->contactReport('camp-abc', 'influenced');

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('GET');
    expect($request->getUri()->getPath())->toBe('/marketing/campaigns/2026-09/camp-abc/reports/contacts/influenced');
});

it('revenue() sends GET /marketing/campaigns/2026-09/{campaignGuid}/reports/revenue', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['revenue' => 12000]),
    ]);

    $result = $client->marketing()->campaigns()->revenue('camp-abc');

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('GET');
    expect($request->getUri()->getPath())->toBe('/marketing/campaigns/2026-09/camp-abc/reports/revenue');
    expect($result)->toBe(['revenue' => 12000]);
});

it('spend() sends GET /marketing/campaigns/2026-09/{campaignGuid}/spend/{spendId}', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['id' => 'spend-1', 'amount' => 1000]),
    ]);

    $client->marketing()->campaigns()->spend('camp-abc', 'spend-1');

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('GET');
    expect($request->getUri()->getPath())->toBe('/marketing/campaigns/2026-09/camp-abc/spend/spend-1');
});

it('version override changes the date segment in the path', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['results' => []]),
    ]);

    $client->marketing()->campaigns('2026-03')->list();

    expect($mock->getLastRequest()->getUri()->getPath())->toBe('/marketing/campaigns/2026-03');
});
