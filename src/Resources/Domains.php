<?php

declare(strict_types=1);

namespace HubSpot\Resources;

use HubSpot\Pagination\Paginator;

final class Domains extends Resource
{
    /** @param  array<string, mixed>  $query
     * @return array<mixed>|object
     */
    public function list(array $query = []): array|object
    {
        return $this->client->request('GET', "/cms/domains/{$this->version()}", [
            'query' => $this->filterNull($query),
        ]);
    }

    /** @param  array<string, mixed>  $query */
    public function all(array $query = []): Paginator
    {
        return Paginator::make(fn (?string $after) => $this->list($query + ['after' => $after]));
    }

    /** @return array<mixed>|object */
    public function get(string $domainId): array|object
    {
        return $this->client->request('GET', "/cms/domains/{$this->version()}/{$domainId}");
    }
}
