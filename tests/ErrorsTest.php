<?php

declare(strict_types=1);

use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Promise\Create;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use HubSpot\Auth\AccessTokenAuth;
use HubSpot\Client;
use HubSpot\Exceptions\ApiException;
use HubSpot\Exceptions\AuthenticationException;
use HubSpot\Exceptions\ConflictException;
use HubSpot\Exceptions\ConnectionException;
use HubSpot\Exceptions\ErrorDetail;
use HubSpot\Exceptions\ForbiddenException;
use HubSpot\Exceptions\NotFoundException;
use HubSpot\Exceptions\RateLimitException;
use HubSpot\Exceptions\ServerException;
use HubSpot\Exceptions\ValidationException;
use HubSpot\ResponseFormat;

/** Run $call and return what it threw, failing the test if it did not throw. */
function caught(callable $call): Throwable
{
    try {
        $call();
    } catch (Throwable $e) {
        return $e;
    }

    throw new RuntimeException('expected an exception');
}

it('maps each status to its exception, all catchable as ApiException', function (int $status, string $class) {
    [$client] = mockClient([jsonResponse($status, ['message' => 'nope'])], maxRetries: 0);

    $e = caught(fn () => $client->request('GET', '/crm/objects/2026-09/contacts/1'));

    expect($e)->toBeInstanceOf($class)->toBeInstanceOf(ApiException::class);
    expect($e->status)->toBe($status);
})->with([
    [400, ValidationException::class],
    [422, ValidationException::class],
    [401, AuthenticationException::class],
    [403, ForbiddenException::class],
    [404, NotFoundException::class],
    [409, ConflictException::class],
    [429, RateLimitException::class],
    [500, ServerException::class],
    [503, ServerException::class],
    [418, ApiException::class],
]);

it('parses the spec Error body into a HubSpotError', function () {
    [$client] = mockClient([jsonResponse(400, [
        'message' => 'Invalid input',
        'category' => 'VALIDATION_ERROR',
        'subCategory' => 'INVALID_PROPERTY',
        'correlationId' => 'aeb5f871-7f07-4993-9211-075dc63e7cbf',
        'context' => ['invalidPropertyName' => ['colour']],
        'links' => ['knowledge-base' => 'https://example.com/kb'],
        'errors' => [['message' => 'Property colour does not exist', 'code' => 'PROPERTY_DOESNT_EXIST', 'in' => 'colour']],
    ])]);

    $error = caught(fn () => $client->request('POST', '/crm/objects/2026-09/contacts'))->error;

    expect($error->message)->toBe('Invalid input');
    expect($error->category)->toBe('VALIDATION_ERROR');
    expect($error->subCategory)->toBe('INVALID_PROPERTY');
    expect($error->correlationId)->toBe('aeb5f871-7f07-4993-9211-075dc63e7cbf');
    expect($error->context)->toBe(['invalidPropertyName' => ['colour']]);
    expect($error->links)->toBe(['knowledge-base' => 'https://example.com/kb']);
    expect($error->errors)->toEqual([new ErrorDetail('Property colour does not exist', 'PROPERTY_DOESNT_EXIST', 'colour')]);
});

it('leaves error null when the body is not a HubSpot error', function () {
    [$client] = mockClient([new Response(502, ['Content-Type' => 'text/html'], '<html>Bad Gateway</html>')], maxRetries: 0);

    $e = caught(fn () => $client->request('GET', '/crm/owners/2026-09'));

    expect($e)->toBeInstanceOf(ServerException::class);
    expect($e->error)->toBeNull();
    expect($e->getMessage())->toBe('HubSpot API request failed with HTTP 502');
});

it('context() carries the client context, the request, and HubSpot ids, without the query string', function () {
    [$client] = mockClient(
        [jsonResponse(404, ['message' => 'gone', 'category' => 'OBJECT_NOT_FOUND', 'correlationId' => 'from-body'])],
        context: ['request_id' => 'req-1', 'portal_id' => 42],
    );

    $e = caught(fn () => $client->request('GET', '/crm/objects/2026-09/contacts/1?idProperty=email'));

    expect($e->context())->toBe([
        'request_id' => 'req-1',
        'portal_id' => 42,
        'method' => 'GET',
        'path' => '/crm/objects/2026-09/contacts/1',
        'status' => 404,
        'category' => 'OBJECT_NOT_FOUND',
        'correlation_id' => 'from-body',
    ]);
});

it('prefers the correlation id response header over the body', function () {
    [$client] = mockClient([jsonResponse(404, ['message' => 'gone', 'correlationId' => 'from-body'], ['X-HubSpot-Correlation-Id' => 'from-header'])]);

    $e = caught(fn () => $client->request('GET', '/x'));

    expect($e->context()['correlation_id'])->toBe('from-header');
});

it('withContext() adds context to a copy and leaves the original alone', function () {
    [$client] = mockClient([jsonResponse(404, ['message' => 'a']), jsonResponse(404, ['message' => 'b'])], context: ['portal_id' => 42]);

    $scoped = $client->withContext(['request_id' => 'job-7']);

    // Resources built from the copy must report through the copy.
    $scopedError = caught(fn () => $scoped->crm()->contacts()->get('1'));
    $originalError = caught(fn () => $client->crm()->contacts()->get('1'));

    expect($scopedError->context())->toMatchArray(['portal_id' => 42, 'request_id' => 'job-7']);
    expect($originalError->context())->not->toHaveKey('request_id');
});

it('withContext() does not reuse resource groups cached on the original', function () {
    [$client] = mockClient([jsonResponse(404, ['message' => 'a'])]);
    $client->crm();

    $scoped = $client->withContext(['request_id' => 'job-7']);
    $e = caught(fn () => $scoped->crm()->contacts()->get('1'));

    expect($e->context()['request_id'])->toBe('job-7');
});

it('ForbiddenException lists missing scopes from the error and its details, once each', function () {
    [$client] = mockClient([jsonResponse(403, [
        'message' => 'This app has not been granted all required scopes',
        'category' => 'MISSING_SCOPES',
        'context' => ['missingScopes' => ['crm.objects.contacts.read']],
        'errors' => [[
            'message' => 'One or more of the following scopes are required.',
            'context' => ['requiredGranularScopes' => ['crm.objects.contacts.read', 'crm.objects.contacts.write']],
        ]],
    ])]);

    $e = caught(fn () => $client->request('GET', '/crm/objects/2026-09/contacts'));

    expect($e->missingScopes())->toBe(['crm.objects.contacts.read', 'crm.objects.contacts.write']);
});

it('ConflictException reads the existing record id from the message', function () {
    [$client] = mockClient([
        jsonResponse(409, ['message' => 'Contact already exists. Existing ID: 12345', 'category' => 'CONFLICT']),
        jsonResponse(409, ['message' => 'Something else conflicted', 'category' => 'CONFLICT']),
    ]);

    expect(caught(fn () => $client->request('POST', '/crm/objects/2026-09/contacts'))->existingId())->toBe('12345');
    expect(caught(fn () => $client->request('POST', '/crm/objects/2026-09/contacts'))->existingId())->toBeNull();
});

it('keeps Retry-After on a RateLimitException', function () {
    [$client] = mockClient([jsonResponse(429, ['message' => 'slow down'], ['Retry-After' => '7'])], maxRetries: 0);

    expect(caught(fn () => $client->request('GET', '/x'))->retryAfter)->toBe(7);
});

it('maps a transport failure to ConnectionException with status 0 and context', function () {
    $failure = new ConnectException('Connection refused', new Request('GET', '/x'));
    [$client] = mockClient([$failure], maxRetries: 0, context: ['request_id' => 'req-1']);

    $e = caught(fn () => $client->request('GET', '/crm/owners/2026-09'));

    expect($e)->toBeInstanceOf(ConnectionException::class);
    expect($e->status)->toBe(0);
    expect($e->getPrevious())->toBe($failure);
    expect($e->context())->toMatchArray(['request_id' => 'req-1', 'path' => '/crm/owners/2026-09']);
});

it('maps an async transport failure to ConnectionException too', function () {
    [$client] = mockClient([new ConnectException('timed out', new Request('GET', '/x'))], maxRetries: 0);

    expect(fn () => $client->requestAsync('GET', '/crm/owners/2026-09')->wait())
        ->toThrow(ConnectionException::class);
});

it('maps statuses from a caller-supplied client that has http_errors on', function () {
    $mock = new MockHandler([jsonResponse(404, ['message' => 'gone'])]);
    $http = new GuzzleHttp\Client(['handler' => HandlerStack::create($mock), 'base_uri' => 'https://api.hubapi.com']);
    $client = new Client(new AccessTokenAuth('t'), $http);

    expect(fn () => $client->request('GET', '/x'))->toThrow(NotFoundException::class);
});

it('lets an app middleware exception through untouched', function () {
    $limited = new DomainException('portal over its own limit');
    $limiter = fn (callable $handler) => fn () => Create::rejectionFor($limited);
    [$client] = mockClient([jsonResponse(200)], maxRetries: 0, middleware: [$limiter]);

    expect(caught(fn () => $client->request('GET', '/x')))->toBe($limited);
});

it('runs app middleware once per attempt, inside retries', function () {
    $calls = 0;
    $counter = function (callable $handler) use (&$calls) {
        return function ($request, array $options) use ($handler, &$calls) {
            $calls++;

            return $handler($request, $options);
        };
    };
    [$client] = mockClient([jsonResponse(500), jsonResponse(200, ['ok' => true])], middleware: [$counter]);

    expect($client->request('GET', '/x'))->toBe(['ok' => true]);
    expect($calls)->toBe(2);
});

it('pushes middleware onto the default stack, inside the retry middleware', function () {
    $calls = 0;
    // Answers without touching the network: a 500 first, then a 200.
    $fake = function (callable $handler) use (&$calls) {
        return function () use (&$calls) {
            return Create::promiseFor(++$calls === 1 ? new Response(500) : new Response(200, [], '{"ok":true}'));
        };
    };
    $client = new Client(new AccessTokenAuth('t'), maxRetries: 1, responseFormat: ResponseFormat::Assoc, middleware: ['fake' => $fake]);

    expect($client->request('GET', '/x'))->toBe(['ok' => true]);
    expect($calls)->toBe(2);
});

it('refuses middleware alongside a caller-supplied http client', function () {
    new Client(new AccessTokenAuth('t'), new GuzzleHttp\Client, middleware: [fn ($h) => $h]);
})->throws(InvalidArgumentException::class);
