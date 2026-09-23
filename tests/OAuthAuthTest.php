<?php

declare(strict_types=1);

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use HubSpot\Auth\OAuthAuth;
use HubSpot\Auth\OAuthClient;
use HubSpot\Auth\TokenSet;
use HubSpot\Exceptions\AuthenticationException;

it('auto-refreshes an expired token and invokes the onTokenRefreshed callback', function (): void {
    $mock = new MockHandler([
        new Response(200, ['Content-Type' => 'application/json'], json_encode([
            'access_token' => 'new-access-token',
            'refresh_token' => 'new-refresh-token',
            'expires_in' => 3600,
        ])),
    ]);
    $oauthClient = new OAuthClient(new GuzzleClient(['handler' => HandlerStack::create($mock)]));

    $auth = new OAuthAuth(
        $oauthClient,
        'client-id',
        'client-secret',
        new TokenSet(
            accessToken: 'old-token',
            refreshToken: 'old-refresh',
            expiresAt: new DateTimeImmutable('-1 hour'),
        ),
    );

    $received = null;
    $auth->onTokenRefreshed(function (TokenSet $ts) use (&$received): void {
        $received = $ts;
    });

    $token = $auth->accessToken();

    expect($token)->toBe('new-access-token')
        ->and($received)->toBeInstanceOf(TokenSet::class)
        ->and($received->accessToken)->toBe('new-access-token')
        ->and($received->refreshToken)->toBe('new-refresh-token');
});

it('returns the existing token without a network call when token is not expired', function (): void {
    // Empty queue: any HTTP attempt throws OutOfBoundsException from MockHandler.
    $mock = new MockHandler([]);
    $oauthClient = new OAuthClient(new GuzzleClient(['handler' => HandlerStack::create($mock)]));

    $auth = new OAuthAuth(
        $oauthClient,
        'client-id',
        'client-secret',
        new TokenSet(
            accessToken: 'current-token',
            refreshToken: 'current-refresh',
            expiresAt: new DateTimeImmutable('+1 hour'),
        ),
    );

    expect($auth->accessToken())->toBe('current-token');
});

it('throws AuthenticationException when the token is expired and no refresh token is stored', function (): void {
    $mock = new MockHandler([]);
    $oauthClient = new OAuthClient(new GuzzleClient(['handler' => HandlerStack::create($mock)]));

    $auth = new OAuthAuth(
        $oauthClient,
        'client-id',
        'client-secret',
        new TokenSet(
            accessToken: 'old-token',
            refreshToken: null,
            expiresAt: new DateTimeImmutable('-1 hour'),
        ),
    );

    expect(fn () => $auth->accessToken())
        ->toThrow(AuthenticationException::class, 'Cannot refresh: no refresh token');
});

it('tokens() returns the current TokenSet (updated after refresh)', function (): void {
    $mock = new MockHandler([
        new Response(200, ['Content-Type' => 'application/json'], json_encode([
            'access_token' => 'refreshed-token',
            'refresh_token' => 'refreshed-refresh',
            'expires_in' => 7200,
        ])),
    ]);
    $oauthClient = new OAuthClient(new GuzzleClient(['handler' => HandlerStack::create($mock)]));

    $expired = new TokenSet(
        accessToken: 'stale',
        refreshToken: 'stale-refresh',
        expiresAt: new DateTimeImmutable('-1 hour'),
    );
    $auth = new OAuthAuth($oauthClient, 'client-id', 'client-secret', $expired);

    $auth->accessToken(); // triggers refresh

    expect($auth->tokens()->accessToken)->toBe('refreshed-token');
});

it('onTokenRefreshed returns self for fluent chaining', function (): void {
    $mock = new MockHandler([]);
    $oauthClient = new OAuthClient(new GuzzleClient(['handler' => HandlerStack::create($mock)]));

    $auth = new OAuthAuth(
        $oauthClient,
        'client-id',
        'client-secret',
        new TokenSet(
            accessToken: 'token',
            refreshToken: 'refresh',
            expiresAt: new DateTimeImmutable('+1 hour'),
        ),
    );

    $result = $auth->onTokenRefreshed(function (TokenSet $ts): void {});

    expect($result)->toBe($auth);
});
