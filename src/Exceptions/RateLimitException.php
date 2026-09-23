<?php

declare(strict_types=1);

namespace HubSpot\Exceptions;

use Throwable;

/**
 * A 429 from HubSpot. `retryAfter` is the Retry-After header in seconds when
 * HubSpot sent one. The retry middleware already honours it on transient
 * retries; this surfaces only after retries are exhausted.
 */
final class RateLimitException extends ApiException
{
    /**
     * @param  array<mixed>|null  $body
     * @param  array<string, mixed>  $context
     */
    public function __construct(
        int $status,
        ?array $body,
        string $message,
        array $context = [],
        ?Throwable $previous = null,
        public readonly ?int $retryAfter = null,
    ) {
        parent::__construct($status, $body, $message, $context, $previous);
    }
}
