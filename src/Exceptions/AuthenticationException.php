<?php

declare(strict_types=1);

namespace HubSpot\Exceptions;

use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\GuzzleException;

/**
 * The OAuth token endpoint rejected the request (bad code, expired refresh
 * token, wrong client secret). Thrown by OAuthClient, which runs before an
 * access token exists and so can't go through Client's normal error mapping.
 */
final class AuthenticationException extends HubSpotException
{
    public static function fromGuzzleException(GuzzleException $e): self
    {
        $status = 0;
        $detail = $e->getMessage();

        if ($e instanceof BadResponseException) {
            $response = $e->getResponse();
            $status = $response->getStatusCode();
            $decoded = json_decode((string) $response->getBody(), true);
            if (is_array($decoded)) {
                $detail = $decoded['message']
                    ?? $decoded['error_description']
                    ?? $decoded['error']
                    ?? $detail;
            }
        }

        return new self("HubSpot OAuth token request failed: {$detail}", $status, $e);
    }
}
