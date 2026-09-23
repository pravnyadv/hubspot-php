<?php

declare(strict_types=1);

namespace HubSpot\Contracts;

interface AuthProvider
{
    /**
     * Return a valid bearer access token, refreshing it first if needed.
     */
    public function accessToken(): string;
}
