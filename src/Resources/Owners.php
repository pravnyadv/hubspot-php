<?php

declare(strict_types=1);

namespace HubSpot\Resources;

use HubSpot\Pagination\Paginator;

final class Owners extends Resource
{
    /** @return array<mixed>|object */
    public function page(int $limit = 100, ?string $after = null): array|object
    {
        return $this->client->request('GET', "/crm/owners/{$this->version()}", [
            'query' => $this->filterNull(['limit' => $limit, 'after' => $after]),
        ]);
    }

    /** @return array<mixed>|object */
    public function get(string $ownerId): array|object
    {
        return $this->client->request('GET', "/crm/owners/{$this->version()}/{$ownerId}");
    }

    public function all(int $limit = 100): Paginator
    {
        return Paginator::make(fn (?string $after) => $this->page($limit, $after));
    }
}
