<?php

declare(strict_types=1);

namespace HubSpot\Exceptions;

use Throwable;

/**
 * A failed HubSpot request. fromResponse() picks the subclass for the status,
 * so callers can catch exactly the failure they handle (NotFoundException,
 * RateLimitException, ...) or this class for any of them.
 */
class ApiException extends HubSpotException
{
    /** The parsed error body, or null when HubSpot sent something else. */
    public readonly ?HubSpotError $error;

    /**
     * @param  array<mixed>|null  $body
     * @param  array<string, mixed>  $context  the client's context plus method and path
     */
    public function __construct(
        public readonly int $status,
        public readonly ?array $body,
        string $message,
        private readonly array $context = [],
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, $status, $previous);
        $this->error = HubSpotError::fromBody($body);
    }

    /** @param  array<string, mixed>  $context */
    public static function fromResponse(int $status, string $rawBody, array $context = [], ?int $retryAfter = null): self
    {
        $body = self::decodeBody($rawBody);
        $args = [$status, $body, self::messageFrom($body) ?? "HubSpot API request failed with HTTP {$status}", $context];

        return match (true) {
            $status === 400, $status === 422 => new ValidationException(...$args),
            $status === 401 => new AuthenticationException(...$args),
            $status === 403 => new ForbiddenException(...$args),
            $status === 404 => new NotFoundException(...$args),
            $status === 409 => new ConflictException(...$args),
            $status === 429 => new RateLimitException(...$args, retryAfter: $retryAfter),
            $status >= 500 => new ServerException(...$args),
            default => new self(...$args),
        };
    }

    /**
     * Structured log context for this failure. Laravel's exception handler
     * merges an exception's context() into the log entry automatically.
     *
     * @return array<string, mixed>
     */
    public function context(): array
    {
        return $this->context + array_filter([
            'status' => $this->status,
            'category' => $this->error?->category,
            'correlation_id' => $this->error?->correlationId,
        ], static fn (mixed $value): bool => $value !== null);
    }

    /** @return array<mixed>|null */
    public static function decodeBody(string $rawBody): ?array
    {
        $body = json_decode($rawBody, true);

        return is_array($body) ? $body : null;
    }

    /**
     * HubSpot reports validation failures as a bracketed JSON array of field
     * errors, either under an `errors` key or appended to `message` as a string.
     * Return the first field error's text so callers surface the real reason
     * ("Property X was invalid") rather than a generic status line.
     *
     * @param  array<mixed>|null  $body
     */
    public static function messageFrom(?array $body): ?string
    {
        if ($body === null) {
            return null;
        }

        if (isset($body['errors'][0]['message']) && is_string($body['errors'][0]['message'])) {
            return $body['errors'][0]['message'];
        }

        $message = $body['message'] ?? null;

        if (is_string($message) && preg_match('/\[.*]/s', $message, $matches) === 1) {
            $decoded = json_decode($matches[0], true);
            if (isset($decoded[0]['message']) && is_string($decoded[0]['message'])) {
                return $decoded[0]['message'];
            }
        }

        return is_string($message) ? $message : null;
    }
}
