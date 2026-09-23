<?php

declare(strict_types=1);

namespace HubSpot\Exceptions;

use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\GuzzleException;

/**
 * A 401 from the API, or the OAuth token endpoint rejecting a code or refresh
 * token (bad code, revoked refresh token, wrong client secret).
 */
final class AuthenticationException extends ApiException
{
    public static function fromGuzzleException(GuzzleException $e): self
    {
        $status = 0;
        $body = null;
        $detail = $e->getMessage();

        if ($e instanceof BadResponseException) {
            $status = $e->getResponse()->getStatusCode();
            $body = self::decodeBody((string) $e->getResponse()->getBody());
            $detail = $body['message'] ?? $body['error_description'] ?? $body['error'] ?? $detail;
        }

        return new self($status, $body, 'HubSpot OAuth token request failed: '.(is_string($detail) ? $detail : 'unknown error'), previous: $e);
    }
}
