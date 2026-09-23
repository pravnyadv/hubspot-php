<?php

declare(strict_types=1);

namespace HubSpot\Resources;

use HubSpot\Pagination\Paginator;

final class Events extends Resource
{
    /**
     * List behavioral event occurrences. Returns results+paging shape.
     *
     * @param  array<string, mixed>  $query
     * @return array<mixed>|object
     */
    public function list(array $query = []): array|object
    {
        return $this->client->request('GET', "/events/event-occurrences/{$this->version()}", [
            'query' => $this->filterNull($query),
        ]);
    }

    /**
     * Paginate all event occurrences transparently.
     *
     * @param  array<string, mixed>  $query
     */
    public function all(array $query = []): Paginator
    {
        return Paginator::make(fn (?string $after) => $this->list($query + ['after' => $after]));
    }

    /**
     * List the event types available to query.
     *
     * @param  array<string, mixed>  $query
     * @return array<mixed>|object
     */
    public function eventTypes(array $query = []): array|object
    {
        return $this->client->request('GET', "/events/event-occurrences/{$this->version()}/event-types", [
            'query' => $this->filterNull($query),
        ]);
    }
}
