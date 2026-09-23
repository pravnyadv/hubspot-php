<?php

declare(strict_types=1);

use HubSpot\Auth\OAuthAuth;
use HubSpot\Auth\OAuthClient;
use HubSpot\Auth\TokenSet;
use HubSpot\Client;
use HubSpot\HubSpot;
use HubSpot\Webhooks\SignatureValidator;

it('withAccessToken returns a Client', function (): void {
    $client = HubSpot::withAccessToken('tok_test');

    expect($client)->toBeInstanceOf(Client::class);
});

it('withOAuth returns a Client', function (): void {
    $auth = new OAuthAuth(
        new OAuthClient,
        'client-id',
        'client-secret',
        new TokenSet('tok', 'refresh', new DateTimeImmutable('+1 hour')),
    );
    $client = HubSpot::withOAuth($auth);

    expect($client)->toBeInstanceOf(Client::class);
});

it('oauth returns an OAuthClient', function (): void {
    $oauthClient = HubSpot::oauth();

    expect($oauthClient)->toBeInstanceOf(OAuthClient::class);
});

it('webhooks returns a SignatureValidator', function (): void {
    $validator = HubSpot::webhooks('my-client-secret');

    expect($validator)->toBeInstanceOf(SignatureValidator::class);
});
