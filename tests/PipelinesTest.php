<?php

declare(strict_types=1);

it('all() sends GET /crm/pipelines/2026-09/{objectType}', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['results' => [['id' => 'pipeline-1']]]),
    ]);

    $result = $client->crm()->pipelines()->all('deals');

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('GET');
    expect($request->getUri()->getPath())->toBe('/crm/pipelines/2026-09/deals');
    expect($result)->toBe(['results' => [['id' => 'pipeline-1']]]);
});

it('get() sends GET /crm/pipelines/2026-09/{objectType}/{pipelineId}', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['id' => 'pipeline-1', 'label' => 'Sales Pipeline']),
    ]);

    $result = $client->crm()->pipelines()->get('deals', 'pipeline-1');

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('GET');
    expect($request->getUri()->getPath())->toBe('/crm/pipelines/2026-09/deals/pipeline-1');
    expect($result)->toBe(['id' => 'pipeline-1', 'label' => 'Sales Pipeline']);
});

it('stages() sends GET /crm/pipelines/2026-09/{objectType}/{pipelineId}/stages', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['results' => [['id' => 'stage-1'], ['id' => 'stage-2']]]),
    ]);

    $result = $client->crm()->pipelines()->stages('deals', 'pipeline-1');

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('GET');
    expect($request->getUri()->getPath())->toBe('/crm/pipelines/2026-09/deals/pipeline-1/stages');
    expect($result)->toBe(['results' => [['id' => 'stage-1'], ['id' => 'stage-2']]]);
});

it('stage() sends GET /crm/pipelines/2026-09/{objectType}/{pipelineId}/stages/{stageId}', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['id' => 'stage-1', 'label' => 'Appointment Scheduled']),
    ]);

    $result = $client->crm()->pipelines()->stage('deals', 'pipeline-1', 'stage-1');

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('GET');
    expect($request->getUri()->getPath())->toBe('/crm/pipelines/2026-09/deals/pipeline-1/stages/stage-1');
    expect($result)->toBe(['id' => 'stage-1', 'label' => 'Appointment Scheduled']);
});

it('version override changes the date segment in the path', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['results' => []]),
    ]);

    $client->crm()->pipelines('2026-03')->all('deals');

    expect($mock->getLastRequest()->getUri()->getPath())->toBe('/crm/pipelines/2026-03/deals');
});
