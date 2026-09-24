<?php

declare(strict_types=1);

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\TransferStats;
use HubSpot\Auth\OAuthClient;

it('logs each request at debug with the client context and timing', function () {
    $logger = new MemoryLogger;
    [$client] = mockClient([jsonResponse(200, ['id' => '1'], ['X-HubSpot-Correlation-Id' => 'corr-1'])], context: ['request_id' => 'req-1'], logger: $logger);

    $client->request('GET', '/crm/objects/2026-09/contacts/1', ['query' => ['idProperty' => 'email']]);

    expect($logger->records)->toHaveCount(1);
    $record = $logger->records[0];
    expect($record['level'])->toBe('debug');
    expect($record['message'])->toBe('HubSpot GET /crm/objects/2026-09/contacts/1 200');
    expect($record['context'])->toMatchArray([
        'request_id' => 'req-1',
        'method' => 'GET',
        'path' => '/crm/objects/2026-09/contacts/1',
        'status' => 200,
        'correlation_id' => 'corr-1',
    ]);
    expect($record['context'])->toHaveKey('duration_ms');
});

it('logs every attempt, so a retried failure shows up as a warning', function () {
    $logger = new MemoryLogger;
    [$client] = mockClient([jsonResponse(503), jsonResponse(200)], logger: $logger);

    $client->request('GET', '/x');

    expect(array_column($logger->records, 'level'))->toBe(['warning', 'debug']);
});

it('logs a 4xx at info and a 429 at warning', function () {
    $logger = new MemoryLogger;
    [$client] = mockClient([jsonResponse(404), jsonResponse(429)], maxRetries: 0, logger: $logger);

    rescue(fn () => $client->request('GET', '/a'));
    rescue(fn () => $client->request('GET', '/b'));

    expect(array_column($logger->records, 'level'))->toBe(['info', 'warning']);
});

it('logs a transport failure as a warning with the error', function () {
    $logger = new MemoryLogger;
    [$client] = mockClient([new ConnectException('Connection refused', new Request('GET', '/x'))], maxRetries: 0, logger: $logger);

    rescue(fn () => $client->request('GET', '/x'));

    expect($logger->records[0]['level'])->toBe('warning');
    expect($logger->records[0]['message'])->toBe('HubSpot GET /x no response');
    expect($logger->records[0]['context']['error'])->toBe('Connection refused');
});

it('still calls a caller on_stats option when logging', function () {
    $seen = null;
    [$client] = mockClient([jsonResponse(200)], logger: new MemoryLogger);

    $client->request('GET', '/x', ['on_stats' => function (TransferStats $stats) use (&$seen) {
        $seen = $stats->getResponse()?->getStatusCode();
    }]);

    expect($seen)->toBe(200);
});

/** Swallow an expected exception so the test can inspect the logs. */
function rescue(callable $call): void
{
    try {
        $call();
    } catch (Throwable) {
    }
}

/**
 * OAuthClient has no AuthProvider yet at the point it's used (it's what gets you
 * one) — the token exchange itself was previously silent no matter what logger
 * Client had, since OAuthClient didn't accept one at all.
 */
it('logs a token exchange the same way Client logs a data-plane call', function () {
    $logger = new MemoryLogger;
    $mock = new MockHandler([jsonResponse(200, ['access_token' => 'a1', 'refresh_token' => 'r1', 'expires_in' => 1800])]);
    $http = new GuzzleClient(['handler' => HandlerStack::create($mock), 'base_uri' => 'https://api.hubapi.com']);
    $client = new OAuthClient($http, context: ['app' => 'formshield'], logger: $logger);

    $client->exchangeAuthorizationCode('cid', 'secret', 'the-code', 'https://app/callback');

    expect($logger->records)->toHaveCount(1);
    $record = $logger->records[0];
    expect($record['level'])->toBe('debug');
    expect($record['message'])->toBe('HubSpot POST /oauth/2026-03/token 200');
    expect($record['context'])->toMatchArray(['app' => 'formshield', 'method' => 'POST', 'status' => 200]);
});

it('logs a failed refresh at warning, same as any other 4xx/5xx', function () {
    $logger = new MemoryLogger;
    $mock = new MockHandler([jsonResponse(500, 'boom')]);
    $http = new GuzzleClient(['handler' => HandlerStack::create($mock), 'base_uri' => 'https://api.hubapi.com']);
    $client = new OAuthClient($http, logger: $logger);

    rescue(fn () => $client->refresh('cid', 'secret', 'old-refresh'));

    expect($logger->records[0]['level'])->toBe('warning');
});
