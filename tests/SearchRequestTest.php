<?php

declare(strict_types=1);

use HubSpot\Search\SearchRequest;

it('toArray() produces the exact HubSpot PublicObjectSearchRequest shape', function () {
    $result = SearchRequest::create()
        ->where('firstname', 'EQ', 'Alice')
        ->where('lastname', 'EQ', 'Smith')
        ->orGroup()
        ->where('email', 'CONTAINS_TOKEN', 'example.com')
        ->properties(['firstname', 'lastname', 'email'])
        ->sort('createdate', 'DESCENDING')
        ->limit(10)
        ->query('Alice')
        ->toArray();

    expect($result)->toBe([
        'filterGroups' => [
            [
                'filters' => [
                    ['propertyName' => 'firstname', 'operator' => 'EQ', 'value' => 'Alice'],
                    ['propertyName' => 'lastname', 'operator' => 'EQ', 'value' => 'Smith'],
                ],
            ],
            [
                'filters' => [
                    ['propertyName' => 'email', 'operator' => 'CONTAINS_TOKEN', 'value' => 'example.com'],
                ],
            ],
        ],
        'properties' => ['firstname', 'lastname', 'email'],
        'sorts' => [['propertyName' => 'createdate', 'direction' => 'DESCENDING']],
        'limit' => 10,
        'query' => 'Alice',
    ]);
});

it('toArray() omits all keys when nothing is set', function () {
    $result = SearchRequest::create()->toArray();

    expect($result)->toBe([]);
    expect($result)->not->toHaveKey('filterGroups');
    expect($result)->not->toHaveKey('properties');
    expect($result)->not->toHaveKey('sorts');
    expect($result)->not->toHaveKey('limit');
    expect($result)->not->toHaveKey('after');
    expect($result)->not->toHaveKey('query');
});

it('omits the value key for valueless operators such as HAS_PROPERTY', function () {
    $result = SearchRequest::create()
        ->where('email', 'HAS_PROPERTY')
        ->toArray();

    $filter = $result['filterGroups'][0]['filters'][0];
    expect($filter)->toHaveKey('propertyName', 'email');
    expect($filter)->toHaveKey('operator', 'HAS_PROPERTY');
    expect($filter)->not->toHaveKey('value');
});

it('sort() defaults direction to DESCENDING', function () {
    $result = SearchRequest::create()
        ->where('createdate', 'GT', '2024-01-01')
        ->sort('createdate')
        ->toArray();

    expect($result['sorts'])->toBe([['propertyName' => 'createdate', 'direction' => 'DESCENDING']]);
});

it('after() is included in the output when set', function () {
    $result = SearchRequest::create()
        ->after('cursor-xyz')
        ->toArray();

    expect($result['after'])->toBe('cursor-xyz');
    expect($result)->not->toHaveKey('filterGroups');
});

it('orGroup() separates filters into distinct groups in the output', function () {
    $result = SearchRequest::create()
        ->where('hs_lead_status', 'EQ', 'NEW')
        ->orGroup()
        ->where('hs_lead_status', 'EQ', 'OPEN')
        ->toArray();

    expect($result['filterGroups'])->toHaveCount(2);
    expect($result['filterGroups'][0]['filters'][0]['value'])->toBe('NEW');
    expect($result['filterGroups'][1]['filters'][0]['value'])->toBe('OPEN');
});

it('where() creates the first group lazily without calling orGroup()', function () {
    $result = SearchRequest::create()
        ->where('firstname', 'EQ', 'Bob')
        ->toArray();

    expect($result['filterGroups'])->toHaveCount(1);
    expect($result['filterGroups'][0]['filters'])->toHaveCount(1);
});

it('properties() omits the key when the list is empty', function () {
    $result = SearchRequest::create()
        ->properties([])
        ->toArray();

    expect($result)->not->toHaveKey('properties');
});
