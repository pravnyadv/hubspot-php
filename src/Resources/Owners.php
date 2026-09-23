<?php

declare(strict_types=1);

namespace HubSpot\Resources;

use HubSpot\Pagination\Paginator;

final class Owners extends Resource
{
    /**
     * $email narrows the page to the owner with that email.
     *
     * @return array<mixed>|object
     */
    public function page(int $limit = 100, ?string $after = null, ?string $email = null, ?bool $archived = null): array|object
    {
        return $this->client->request('GET', "/crm/owners/{$this->version()}", [
            'query' => $this->filterNull(['limit' => $limit, 'after' => $after, 'email' => $email, 'archived' => $archived]),
        ]);
    }

    /**
     * @param  'id'|'userId'|null  $idProperty  'userId' looks the owner up by their HubSpot user id
     * @return array<mixed>|object
     */
    public function get(string $ownerId, ?string $idProperty = null): array|object
    {
        return $this->client->request('GET', "/crm/owners/{$this->version()}/{$ownerId}", [
            'query' => $this->filterNull(['idProperty' => $idProperty]),
        ]);
    }

    /**
     * Like get(), but null when there is no such owner.
     *
     * @param  'id'|'userId'|null  $idProperty
     * @return array<mixed>|object|null
     */
    public function find(string $ownerId, ?string $idProperty = null): array|object|null
    {
        return $this->orNull(fn () => $this->get($ownerId, $idProperty));
    }

    public function all(int $limit = 100): Paginator
    {
        return Paginator::make(fn (?string $after) => $this->page($limit, $after));
    }
}
