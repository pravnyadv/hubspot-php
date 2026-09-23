<?php

declare(strict_types=1);

namespace HubSpot\Resources;

use HubSpot\Pagination\Paginator;

final class Scheduler extends Resource
{
    // -------------------------------------------------------------------------
    // Calendar
    // -------------------------------------------------------------------------

    /**
     * Create a meeting from an external calendar sync. The endpoint is POST-only
     * (it creates a meeting event, it does not read the calendar).
     *
     * @param  array<string, mixed>  $body
     * @return array<mixed>|object
     */
    public function calendar(string $organizerUserId, array $body): array|object
    {
        return $this->client->request('POST', "/scheduler/{$this->version()}/meetings/calendar", [
            'query' => ['organizerUserId' => $organizerUserId],
            'json' => $body,
        ]);
    }

    // -------------------------------------------------------------------------
    // Meeting links
    // -------------------------------------------------------------------------

    /**
     * List all meeting scheduling links.
     *
     * @param  array<string, mixed>  $query
     * @return array<mixed>|object
     */
    public function list(array $query = []): array|object
    {
        return $this->client->request('GET', "/scheduler/{$this->version()}/meetings/meeting-links", [
            'query' => $this->filterNull($query),
        ]);
    }

    public function all(): Paginator
    {
        return Paginator::make(fn (?string $after) => $this->list(['after' => $after]));
    }

    /**
     * Fetch a single meeting link by its public slug.
     *
     * @return array<mixed>|object
     */
    public function get(string $slug): array|object
    {
        return $this->client->request('GET', "/scheduler/{$this->version()}/meetings/meeting-links/book/{$slug}");
    }

    /**
     * Fetch the availability page data for a meeting link slug.
     *
     * @return array<mixed>|object
     */
    public function availability(string $slug): array|object
    {
        return $this->client->request('GET', "/scheduler/{$this->version()}/meetings/meeting-links/book/availability-page/{$slug}");
    }

    /**
     * Book a meeting.
     *
     * @param  array<string, mixed>  $body
     * @return array<mixed>|object
     */
    public function book(array $body): array|object
    {
        return $this->client->request('POST', "/scheduler/{$this->version()}/meetings/meeting-links/book", [
            'json' => $body,
        ]);
    }
}
