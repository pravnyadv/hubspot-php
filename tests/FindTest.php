<?php

declare(strict_types=1);

use HubSpot\Exceptions\RateLimitException;
use HubSpot\Exceptions\ServerException;

it('find() returns the record like get()', function () {
    [$client] = mockClient([jsonResponse(200, ['id' => '1'])]);

    expect($client->crm()->contacts()->find('1'))->toBe(['id' => '1']);
});

it('find() returns null on a 404 from every resource that has it', function (Closure $find) {
    [$client] = mockClient([jsonResponse(404, ['message' => 'not found', 'category' => 'OBJECT_NOT_FOUND'])]);

    expect($find($client))->toBeNull();
})->with([
    'crm objects' => [fn ($c) => $c->crm()->contacts()->find('1')],
    'owners' => [fn ($c) => $c->crm()->owners()->find('1')],
    'lists' => [fn ($c) => $c->crm()->lists()->find('1')],
    'properties' => [fn ($c) => $c->crm()->properties()->find('contacts', 'colour')],
    'crud resources' => [fn ($c) => $c->cms()->blogAuthors()->find('1')],
]);

it('find() still throws on a rate limit, so it never reads as "not there"', function () {
    [$client] = mockClient([jsonResponse(429, ['message' => 'slow down'])], maxRetries: 0);

    expect(fn () => $client->crm()->contacts()->find('1'))->toThrow(RateLimitException::class);
});

it('find() still throws on a server error', function () {
    [$client] = mockClient([jsonResponse(500, ['message' => 'boom'])], maxRetries: 0);

    expect(fn () => $client->crm()->contacts()->find('1'))->toThrow(ServerException::class);
});

it('find() passes every get() argument through', function () {
    [$client, $mock] = mockClient([jsonResponse(200, ['id' => '1'])]);

    $client->crm()->contacts()->find('jane@example.com', ['email'], ['lifecyclestage'], ['deals'], 'email');

    $request = $mock->getLastRequest();
    expect($request->getUri()->getPath())->toBe('/crm/objects/2026-09/contacts/jane%40example.com');
    parse_str($request->getUri()->getQuery(), $query);
    expect($query)->toBe([
        'properties' => 'email',
        'propertiesWithHistory' => 'lifecyclestage',
        'associations' => 'deals',
        'idProperty' => 'email',
    ]);
});
