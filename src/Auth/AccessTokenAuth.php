<?php

declare(strict_types=1);

namespace HubSpot\Auth;

use HubSpot\Contracts\AuthProvider;

/**
 * A static bearer token: a private app token or service key. Never expires
 * from this library's point of view — HubSpot private app tokens don't rotate.
 */
final class AccessTokenAuth implements AuthProvider
{
    public function __construct(private readonly string $token) {}

    public function accessToken(): string
    {
        return $this->token;
    }
}
