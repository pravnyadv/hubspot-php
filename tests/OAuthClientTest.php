<?php

declare(strict_types=1);

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use HubSpot\Auth\OAuthClient;
use HubSpot\Auth\TokenSet;
use HubSpot\Exceptions\AuthenticationException;
use HubSpot\Middleware\RetryMiddleware;

/** @return array{0: OAuthClient, 1: MockHandler} */
function oauthClient(array $responses): array
{
    $mock = new MockHandler($responses);
    $http = new GuzzleClient(['handler' => HandlerStack::create($mock), 'base_uri' => 'https://api.hubapi.com']);

    return [new OAuthClient($http), $mock];
}

it('exchanges an authorization code for a TokenSet at the 2026-03 token endpoint', function () {
    [$client, $mock] = oauthClient([
        new Response(200, [], (string) json_encode(['access_token' => 'a1', 'refresh_token' => 'r1', 'expires_in' => 1800])),
    ]);

    $tokens = $client->exchangeAuthorizationCode('cid', 'secret', 'the-code', 'https://app/callback');

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('POST');
    expect($request->getUri()->getPath())->toBe('/oauth/2026-03/token');
    expect((string) $request->getBody())->toContain('grant_type=authorization_code');
    expect($tokens)->toBeInstanceOf(TokenSet::class);
    expect($tokens->accessToken)->toBe('a1');
    expect($tokens->refreshToken)->toBe('r1');
});

it('refreshes and carries the old refresh token forward when none is returned', function () {
    [$client] = oauthClient([
        new Response(200, [], (string) json_encode(['access_token' => 'a2', 'expires_in' => 1800])),
    ]);

    $tokens = $client->refresh('cid', 'secret', 'old-refresh');

    expect($tokens->accessToken)->toBe('a2');
    expect($tokens->refreshToken)->toBe('old-refresh');
});

it('retries a 429 on token refresh (a POST) then succeeds', function () {
    $mock = new MockHandler([
        new Response(429, [], 'slow down'),
        new Response(200, [], (string) json_encode(['access_token' => 'a', 'expires_in' => 1800])),
    ]);
    $stack = HandlerStack::create($mock);
    $stack->push(RetryMiddleware::create(3, baseDelayMs: 1));
    $client = new OAuthClient(new GuzzleClient(['handler' => $stack, 'base_uri' => 'https://api.hubapi.com']));

    $tokens = $client->refresh('cid', 'secret', 'r');

    expect($tokens->accessToken)->toBe('a');
    expect($mock->count())->toBe(0);
});

it('throws AuthenticationException on a 2xx token response with no access_token', function () {
    [$client] = oauthClient([
        new Response(200, [], (string) json_encode(['error' => 'invalid_grant'])),
    ]);

    expect(fn () => $client->refresh('cid', 'secret', 'r'))
        ->toThrow(AuthenticationException::class);
});

it('reads token metadata from the introspection endpoint', function () {
    [$client, $mock] = oauthClient([
        new Response(200, [], (string) json_encode(['hub_id' => 123, 'scopes' => ['crm.objects.contacts.read']])),
    ]);

    $info = $client->tokenInfo('access-token-xyz');

    expect($mock->getLastRequest()->getUri()->getPath())->toBe('/oauth/v1/access-tokens/access-token-xyz');
    expect($info)->toBe(['hub_id' => 123, 'scopes' => ['crm.objects.contacts.read']]);
});

it('builds an authorization URL with scopes and state', function () {
    [$client] = oauthClient([]);

    $url = $client->authorizationUrl('cid', 'https://app/callback', ['crm.objects.contacts.read', 'crm.lists.read'], 'state-123');

    expect($url)->toStartWith('https://app.hubspot.com/oauth/authorize?');
    expect($url)->toContain('client_id=cid');
    expect($url)->toContain('scope=crm.objects.contacts.read+crm.lists.read');
    expect($url)->toContain('state=state-123');
});
