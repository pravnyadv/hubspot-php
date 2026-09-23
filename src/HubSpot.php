<?php

declare(strict_types=1);

namespace HubSpot;

use GuzzleHttp\ClientInterface;
use HubSpot\Auth\AccessTokenAuth;
use HubSpot\Auth\OAuthAuth;
use HubSpot\Auth\OAuthClient;
use HubSpot\Webhooks\SignatureValidator;
use Psr\Log\LoggerInterface;

final class HubSpot
{
    /**
     * @param  array<string, mixed>  $context
     * @param  array<int|string, callable>  $middleware
     */
    public static function withAccessToken(
        string $token,
        string $version = Client::DEFAULT_VERSION,
        int $maxRetries = 3,
        ResponseFormat $responseFormat = ResponseFormat::Object,
        array $context = [],
        ?LoggerInterface $logger = null,
        array $middleware = [],
    ): Client {
        return new Client(new AccessTokenAuth($token), null, $version, $maxRetries, $responseFormat, $context, $logger, $middleware);
    }

    /**
     * @param  array<string, mixed>  $context
     * @param  array<int|string, callable>  $middleware
     */
    public static function withOAuth(
        OAuthAuth $auth,
        string $version = Client::DEFAULT_VERSION,
        int $maxRetries = 3,
        ResponseFormat $responseFormat = ResponseFormat::Object,
        array $context = [],
        ?LoggerInterface $logger = null,
        array $middleware = [],
    ): Client {
        return new Client($auth, null, $version, $maxRetries, $responseFormat, $context, $logger, $middleware);
    }

    public static function oauth(
        ?ClientInterface $http = null,
        string $version = '2026-03',
    ): OAuthClient {
        return new OAuthClient($http, $version);
    }

    public static function webhooks(string $clientSecret): SignatureValidator
    {
        return new SignatureValidator($clientSecret);
    }
}
