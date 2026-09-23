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
    /** @param  array<mixed>|null  $body */
    public function __construct(
        public readonly ?int $retryAfter,
        int $status,
        ?array $body,
        string $message,
        ?Throwable $previous = null,
    ) {
        parent::__construct($status, $body, $message, $previous);
    }
}
