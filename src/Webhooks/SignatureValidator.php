<?php

declare(strict_types=1);

namespace HubSpot\Webhooks;

final class SignatureValidator
{
    public function __construct(private readonly string $clientSecret) {}

    /**
     * Validates a HubSpot v3 webhook signature (HMAC-SHA256, base64-encoded).
     * Rejects requests whose timestamp is older than $maxAgeSeconds to prevent replay attacks.
     */
    public function isValidV3(
        string $method,
        string $uri,
        string $body,
        string $signature,
        string $timestamp,
        int $maxAgeSeconds = 300,
        ?int $nowMs = null,
    ): bool {
        $now = $nowMs ?? (int) round(microtime(true) * 1000);

        if ($now - (int) $timestamp > $maxAgeSeconds * 1000) {
            return false;
        }

        $source = $method.$uri.$body.$timestamp;
        $expected = base64_encode(hash_hmac('sha256', $source, $this->clientSecret, true));

        return hash_equals($expected, $signature);
    }

    /**
     * Validates a HubSpot v2 webhook signature (SHA-256 hex digest, no replay protection).
     */
    public function isValidV2(
        string $method,
        string $uri,
        string $body,
        string $signature,
    ): bool {
        $source = $this->clientSecret.$method.$uri.$body;
        $expected = hash('sha256', $source);

        return hash_equals($expected, $signature);
    }
}
