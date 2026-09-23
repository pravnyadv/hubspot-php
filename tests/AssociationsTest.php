<?php

declare(strict_types=1);

it('batchRead() sends POST /crm/associations/2026-09/{fromType}/{toType}/batch/read with inputs', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['status' => 'COMPLETE', 'results' => [['from' => ['id' => '1'], 'to' => [['toObjectId' => '2', 'associationTypes' => []]]]]]),
    ]);

    $inputs = [['id' => '1']];
    $result = $client->crm()->associations()->batchRead('contacts', 'companies', $inputs);

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('POST');
    expect($request->getUri()->getPath())->toBe('/crm/associations/2026-09/contacts/companies/batch/read');

    $body = json_decode((string) $request->getBody(), true);
    expect($body)->toBe(['inputs' => $inputs]);

    expect($result['status'])->toBe('COMPLETE');
});

it('batchCreate() sends POST /crm/associations/2026-09/{fromType}/{toType}/batch/create with inputs', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['status' => 'COMPLETE', 'results' => []]),
    ]);

    $inputs = [['_from' => ['id' => '1'], 'to' => ['id' => '2'], 'types' => [['associationCategory' => 'HUBSPOT_DEFINED', 'associationTypeId' => 1]]]];
    $result = $client->crm()->associations()->batchCreate('contacts', 'companies', $inputs);

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('POST');
    expect($request->getUri()->getPath())->toBe('/crm/associations/2026-09/contacts/companies/batch/create');

    $body = json_decode((string) $request->getBody(), true);
    expect($body)->toBe(['inputs' => $inputs]);

    expect($result['status'])->toBe('COMPLETE');
});

it('batchArchive() sends POST /crm/associations/2026-09/{fromType}/{toType}/batch/archive with inputs', function () {
    [$client, $mock] = mockClient([
        jsonResponse(204, []),
    ]);

    $inputs = [['_from' => ['id' => '1'], 'to' => [['id' => '2']]]];
    $client->crm()->associations()->batchArchive('contacts', 'companies', $inputs);

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('POST');
    expect($request->getUri()->getPath())->toBe('/crm/associations/2026-09/contacts/companies/batch/archive');

    $body = json_decode((string) $request->getBody(), true);
    expect($body)->toBe(['inputs' => $inputs]);
});

it('labels() sends GET /crm/associations/2026-09/{fromType}/{toType}/labels', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['results' => [['category' => 'HUBSPOT_DEFINED', 'typeId' => 1, 'label' => null]]]),
    ]);

    $result = $client->crm()->associations()->labels('contacts', 'companies');

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('GET');
    expect($request->getUri()->getPath())->toBe('/crm/associations/2026-09/contacts/companies/labels');

    expect($result['results'])->toHaveCount(1);
});

it('associateDefault() sends PUT /crm/objects/2026-09/{fromType}/{fromId}/associations/default/{toType}/{toId}', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['fromObjectTypeId' => '0-1', 'fromObjectId' => 101, 'toObjectTypeId' => '0-2', 'toObjectId' => 202, 'labels' => []]),
    ]);

    $result = $client->crm()->associations()->associateDefault('contacts', '101', 'companies', '202');

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('PUT');
    expect($request->getUri()->getPath())->toBe('/crm/objects/2026-09/contacts/101/associations/default/companies/202');

    expect($result['fromObjectId'])->toBe(101);
    expect($result['toObjectId'])->toBe(202);
});

it('version override changes the date segment in the path', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['results' => []]),
    ]);

    $client->crm()->associations('2026-03')->labels('contacts', 'deals');

    expect($mock->getLastRequest()->getUri()->getPath())->toBe('/crm/associations/2026-03/contacts/deals/labels');
});
