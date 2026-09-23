<?php

declare(strict_types=1);

namespace HubSpot\Auth;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\HandlerStack;
use HubSpot\Exceptions\AuthenticationException;
use HubSpot\Middleware\RetryMiddleware;

/**
 * The OAuth token endpoint itself — exchanging a code or refresh token for
 * an access token. Deliberately separate from Client: this call happens
 * before you have an access token, so it can't depend on AuthProvider.
 */
final class OAuthClient
{
    private readonly ClientInterface $http;

    public function __construct(
        ?ClientInterface $http = null,
        private readonly string $version = '2026-03',
    ) {
        // Retry transient failures (429/5xx) on token calls too: a failed refresh
        // cascades into every subsequent API request. http_errors stays on so a
        // 4xx still surfaces as an AuthenticationException.
        $stack = HandlerStack::create();
        $stack->push(RetryMiddleware::create());
        $this->http = $http ?? new GuzzleClient([
            'base_uri' => 'https://api.hubapi.com',
            'handler' => $stack,
        ]);
    }

    public function exchangeAuthorizationCode(
        string $clientId,
        string $clientSecret,
        string $code,
        string $redirectUri,
    ): TokenSet {
        return $this->requestToken([
            'grant_type' => 'authorization_code',
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'code' => $code,
            'redirect_uri' => $redirectUri,
        ]);
    }

    public function refresh(string $clientId, string $clientSecret, string $refreshToken): TokenSet
    {
        return $this->requestToken([
            'grant_type' => 'refresh_token',
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'refresh_token' => $refreshToken,
        ], fallbackRefreshToken: $refreshToken);
    }

    /** @param  list<string>  $scopes */
    public function authorizationUrl(string $clientId, string $redirectUri, array $scopes, ?string $state = null): string
    {
        $query = [
            'client_id' => $clientId,
            'redirect_uri' => $redirectUri,
            'scope' => implode(' ', $scopes),
        ];

        if ($state !== null) {
            $query['state'] = $state;
        }

        return 'https://app.hubspot.com/oauth/authorize?'.http_build_query($query);
    }

    /**
     * Token metadata (hub id, user, scopes, expiry) for an access token. Still a
     * numbered path: HubSpot has not shipped a date-based introspection endpoint.
     *
     * @return array<string, mixed>
     */
    public function tokenInfo(string $accessToken): array
    {
        try {
            $response = $this->http->request('GET', "oauth/v1/access-tokens/{$accessToken}", [
                'headers' => ['Accept' => 'application/json'],
            ]);
        } catch (GuzzleException $e) {
            throw AuthenticationException::fromGuzzleException($e);
        }

        $data = json_decode((string) $response->getBody(), true);

        return is_array($data) ? $data : [];
    }

    /** @param  array<string, string>  $form */
    private function requestToken(array $form, ?string $fallbackRefreshToken = null): TokenSet
    {
        try {
            $response = $this->http->request('POST', "oauth/{$this->version}/token", [
                'form_params' => $form,
                'headers' => ['Accept' => 'application/json'],
            ]);
        } catch (GuzzleException $e) {
            throw AuthenticationException::fromGuzzleException($e);
        }

        $data = json_decode((string) $response->getBody(), true);

        if (! is_array($data) || ! isset($data['access_token'])) {
            throw new AuthenticationException(
                'HubSpot token response did not contain an access_token.',
                $response->getStatusCode(),
            );
        }

        return TokenSet::fromTokenResponse($data, $fallbackRefreshToken);
    }
}
