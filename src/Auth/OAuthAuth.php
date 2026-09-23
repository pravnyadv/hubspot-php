<?php

declare(strict_types=1);

namespace HubSpot\Auth;

use HubSpot\Contracts\AuthProvider;
use HubSpot\Exceptions\AuthenticationException;

final class OAuthAuth implements AuthProvider
{
    private ?\Closure $onRefreshed = null;

    public function __construct(
        private readonly OAuthClient $oauth,
        private readonly string $clientId,
        private readonly string $clientSecret,
        private TokenSet $tokens,
    ) {}

    public function accessToken(): string
    {
        if ($this->tokens->isExpired()) {
            if ($this->tokens->refreshToken === null) {
                throw new AuthenticationException('Cannot refresh: no refresh token', 0);
            }
            $new = $this->oauth->refresh($this->clientId, $this->clientSecret, $this->tokens->refreshToken);
            $this->tokens = $new;
            if ($this->onRefreshed !== null) {
                ($this->onRefreshed)($new);
            }
        }

        return $this->tokens->accessToken;
    }

    // Registers a callback invoked after every successful token refresh.
    // The app must persist the new TokenSet; this package does not.
    public function onTokenRefreshed(callable $callback): self
    {
        $this->onRefreshed = $callback(...);

        return $this;
    }

    public function tokens(): TokenSet
    {
        return $this->tokens;
    }
}
