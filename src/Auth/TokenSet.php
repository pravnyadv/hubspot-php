<?php

declare(strict_types=1);

namespace HubSpot\Auth;

/**
 * An OAuth access/refresh token pair with its expiry, as returned by the
 * token endpoint. Immutable — a refresh produces a new instance.
 */
final class TokenSet
{
    public function __construct(
        public readonly string $accessToken,
        public readonly ?string $refreshToken,
        public readonly \DateTimeImmutable $expiresAt,
    ) {}

    /** @param  array<string, mixed>  $response */
    public static function fromTokenResponse(array $response, ?string $fallbackRefreshToken = null): self
    {
        return new self(
            accessToken: $response['access_token'],
            refreshToken: $response['refresh_token'] ?? $fallbackRefreshToken,
            expiresAt: (new \DateTimeImmutable)->modify('+'.((int) $response['expires_in']).' seconds'),
        );
    }

    /**
     * @param  int  $leewaySeconds  Treat the token as expired this many seconds early,
     *                              so a request started just before expiry doesn't fail mid-flight.
     */
    public function isExpired(int $leewaySeconds = 30): bool
    {
        return $this->expiresAt <= new \DateTimeImmutable("+{$leewaySeconds} seconds");
    }
}
