<?php

declare(strict_types=1);

use HubSpot\Pagination\Paginator;

it('list() hits the beta-versioned base path with limit and no after', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['results' => [['id' => 'f1']], 'total' => 1]),
    ]);

    $result = $client->marketing()->forms()->list(50);

    $uri = $mock->getLastRequest()->getUri();
    expect($uri->getPath())->toBe('/marketing/forms/2026-09-beta');
    parse_str($uri->getQuery(), $query);
    expect($query['limit'])->toBe('50');
    expect(isset($query['after']))->toBeFalse();
    expect($result['results'])->toBe([['id' => 'f1']]);
});

it('list() passes after cursor when supplied', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['results' => []]),
    ]);

    $client->marketing()->forms()->list(100, 'cursor-abc');

    parse_str($mock->getLastRequest()->getUri()->getQuery(), $query);
    expect($query['after'])->toBe('cursor-abc');
});

it('get() hits the beta-versioned path for a specific form id', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['id' => 'form-xyz', 'name' => 'Contact']),
    ]);

    $result = $client->marketing()->forms()->get('form-xyz');

    expect($mock->getLastRequest()->getUri()->getPath())->toBe('/marketing/forms/2026-09-beta/form-xyz');
    expect($result['id'])->toBe('form-xyz');
});

it('a per-resource version override replaces the date segment', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['results' => []]),
    ]);

    $client->marketing()->forms('2026-03')->list();

    expect($mock->getLastRequest()->getUri()->getPath())->toBe('/marketing/forms/2026-03');
});

it('all() returns a Paginator that walks paging.next.after', function () {
    [$client] = mockClient([
        jsonResponse(200, [
            'results' => [['id' => 'f1']],
            'paging' => ['next' => ['after' => 'cursor-1']],
        ]),
        jsonResponse(200, [
            'results' => [['id' => 'f2']],
        ]),
    ]);

    $paginator = $client->marketing()->forms()->all();
    expect($paginator)->toBeInstanceOf(Paginator::class);
    expect($paginator->all())->toBe([['id' => 'f1'], ['id' => 'f2']]);
});
