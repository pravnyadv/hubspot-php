<?php

declare(strict_types=1);

namespace HubSpot\Resources;

final class AccountInfo extends Resource
{
    /** @return array<mixed>|object */
    public function details(): array|object
    {
        return $this->client->request('GET', "/account-info/{$this->version()}/details");
    }
}
