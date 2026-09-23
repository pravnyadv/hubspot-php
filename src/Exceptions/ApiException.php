<?php

declare(strict_types=1);

namespace HubSpot\Exceptions;

use Throwable;

/**
 * A non-2xx response from HubSpot. Carries the HTTP status and the decoded
 * response body so callers can inspect HubSpot's error detail.
 */
class ApiException extends HubSpotException
{
    /** @param  array<mixed>|null  $body */
    public function __construct(
        public readonly int $status,
        public readonly ?array $body,
        string $message,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, $status, $previous);
    }

    public static function fromResponse(int $status, string $rawBody, ?Throwable $previous = null): self
    {
        $body = self::decodeBody($rawBody);

        return new self(
            $status,
            $body,
            self::messageFrom($body) ?? "HubSpot API request failed with HTTP {$status}",
            $previous,
        );
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
