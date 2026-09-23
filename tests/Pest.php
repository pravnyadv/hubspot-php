<?php

declare(strict_types=1);

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use HubSpot\Auth\AccessTokenAuth;
use HubSpot\Client;
use HubSpot\Middleware\RetryMiddleware;
use HubSpot\ResponseFormat;

/**
 * Build a Client backed by a Guzzle MockHandler, so no test hits the network.
 * Returns the client and the MockHandler; assert on the outgoing request with
 * `$mock->getLastRequest()`. Retry backoff uses a 1ms base so retry tests
 * don't sleep.
 *
 * Defaults to Assoc format so response assertions can use plain arrays; the
 * shipped client default is Object (see ResponseFormatTest). Pass $format to
 * exercise object mode.
 *
 * @param  list<Response>  $responses  Queued responses, one popped per attempt.
 * @return array{0: Client, 1: MockHandler}
 */
function mockClient(array $responses, int $maxRetries = 3, ResponseFormat $format = ResponseFormat::Assoc): array
{
    $mock = new MockHandler($responses);
    $stack = HandlerStack::create($mock);
    $stack->push(RetryMiddleware::create($maxRetries, baseDelayMs: 1));

    $http = new GuzzleClient([
        'base_uri' => 'https://api.hubapi.com',
        'handler' => $stack,
        'http_errors' => false,
    ]);

    return [new Client(new AccessTokenAuth('test-token'), $http, responseFormat: $format), $mock];
}

/**
 * A JSON response for the mock queue.
 *
 * @param  array<mixed>|string  $body
 * @param  array<string, string>  $headers
 */
function jsonResponse(int $status, array|string $body = [], array $headers = []): Response
{
    $payload = is_string($body) ? $body : (string) json_encode($body);

    return new Response($status, ['Content-Type' => 'application/json'] + $headers, $payload);
}
