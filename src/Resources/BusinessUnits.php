<?php

declare(strict_types=1);

namespace HubSpot\Resources;

final class BusinessUnits extends Resource
{
    /**
     * Return the business units accessible to a given user.
     *
     * @return array<mixed>|object
     */
    public function forUser(string $userId): array|object
    {
        return $this->client->request('GET', "/business-units/public/{$this->version()}/business-units/user/{$userId}");
    }
}
