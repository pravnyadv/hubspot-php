<?php

declare(strict_types=1);

namespace HubSpot\Middleware;

use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Middleware;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Guzzle retry middleware for HubSpot's transient failures. A 429 is retried on
 * any method (rate-limited, so the request was not processed). A 5xx or dropped
 * connection is retried only on idempotent methods (GET/HEAD/PUT/DELETE), never
 * a POST or PATCH, since the write may have gone through and retrying would
 * duplicate it. Honours Retry-After, otherwise backs off exponentially.
 *
 * Works whether the client runs with http_errors on or off: it reads the status
 * from the response, or from a RequestException's response when http_errors made
 * Guzzle throw before the response reached the decider.
 */
final class RetryMiddleware
{
    public static function create(int $maxRetries = 3, int $baseDelayMs = 1000): callable
    {
        return Middleware::retry(self::decider($maxRetries), self::delay($baseDelayMs));
    }

    private static function decider(int $maxRetries): callable
    {
        return static function (
            int $retries,
            RequestInterface $request,
            ?ResponseInterface $response,
            ?\Throwable $exception,
        ) use ($maxRetries): bool {
            if ($retries >= $maxRetries) {
                return false;
            }

            $status = $response?->getStatusCode();
            if ($status === null && $exception instanceof BadResponseException) {
                $status = $exception->getResponse()->getStatusCode();
            }

            // 429 means HubSpot rate-limited the request without processing it,
            // so it is safe to retry on any method.
            if ($status === 429) {
                return true;
            }

            // A 5xx or a dropped connection may mean the request was actually
            // processed, so retry those only for idempotent methods, never a
            // POST or PATCH, to avoid duplicate writes.
            if (! self::isIdempotent($request->getMethod())) {
                return false;
            }

            return $exception instanceof ConnectException || ($status !== null && $status >= 500);
        };
    }

    private static function isIdempotent(string $method): bool
    {
        return in_array(strtoupper($method), ['GET', 'HEAD', 'PUT', 'DELETE', 'OPTIONS', 'TRACE'], true);
    }

    /**
     * Milliseconds to wait before attempt $retries (1-based): the Retry-After
     * header when HubSpot sends one, otherwise exponential backoff off the base.
     */
    private static function delay(int $baseDelayMs): callable
    {
        return static function (int $retries, ?ResponseInterface $response) use ($baseDelayMs): int {
            if ($response !== null && $response->hasHeader('Retry-After')) {
                $retryAfter = (int) $response->getHeaderLine('Retry-After');
                if ($retryAfter > 0) {
                    return $retryAfter * 1000;
                }
            }

            return (int) ($baseDelayMs * 2 ** ($retries - 1));
        };
    }
}
