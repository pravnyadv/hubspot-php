<?php

declare(strict_types=1);

namespace HubSpot\Resources;

use HubSpot\Pagination\Paginator;

final class MarketingEvents extends Resource
{
    private function base(): string
    {
        return "/marketing/marketing-events/{$this->version()}";
    }

    // ---------------------------------------------------------------------------
    // Events collection
    // ---------------------------------------------------------------------------

    /**
     * @param  array<string, mixed>  $query
     * @return array<mixed>|object
     */
    public function list(array $query = []): array|object
    {
        return $this->client->request('GET', $this->base(), [
            'query' => $this->filterNull($query),
        ]);
    }

    /** @param  array<string, mixed>  $query */
    public function all(array $query = []): Paginator
    {
        return Paginator::make(fn (?string $after) => $this->list($query + ['after' => $after]));
    }

    /** @return array<mixed>|object */
    public function get(string $externalEventId): array|object
    {
        return $this->client->request('GET', "{$this->base()}/events/{$externalEventId}");
    }

    /**
     * Search marketing events by external event id.
     *
     * @return array<mixed>|object
     */
    public function search(string $q): array|object
    {
        return $this->client->request('GET', "{$this->base()}/events/search", [
            'query' => ['q' => $q],
        ]);
    }

    /**
     * Create or update events keyed by externalEventId + externalAccountId.
     *
     * @param  list<array<string, mixed>>  $inputs
     * @return array<mixed>|object
     */
    public function upsert(array $inputs): array|object
    {
        return $this->batch("{$this->base()}/events/upsert", $inputs);
    }

    /**
     * Bulk-delete events by external id.
     *
     * @param  list<array<string, mixed>>  $inputs
     */
    public function batchDelete(array $inputs): void
    {
        $this->batch("{$this->base()}/events/delete", $inputs);
    }

    // ---------------------------------------------------------------------------
    // Event lifecycle
    // ---------------------------------------------------------------------------

    /** @return array<mixed>|object */
    public function cancel(string $externalEventId): array|object
    {
        return $this->client->request('POST', "{$this->base()}/events/{$externalEventId}/cancel");
    }

    /** @return array<mixed>|object */
    public function complete(string $externalEventId): array|object
    {
        return $this->client->request('POST', "{$this->base()}/events/{$externalEventId}/complete");
    }

    // ---------------------------------------------------------------------------
    // Batch operations (object-id based)
    // ---------------------------------------------------------------------------

    /**
     * @param  list<array<string, mixed>>  $inputs
     * @return array<mixed>|object
     */
    public function batchArchive(array $inputs): array|object
    {
        return $this->batch("{$this->base()}/batch/archive", $inputs);
    }

    /**
     * @param  list<array<string, mixed>>  $inputs
     * @return array<mixed>|object
     */
    public function batchUpdate(array $inputs): array|object
    {
        return $this->batch("{$this->base()}/batch/update", $inputs);
    }

    // ---------------------------------------------------------------------------
    // Attendance recording
    // ---------------------------------------------------------------------------

    /**
     * Record attendance by contact id/vid.
     *
     * @param  array<string, mixed>  $body
     * @return array<mixed>|object
     */
    public function recordAttendance(string $externalEventId, string $subscriberState, array $body): array|object
    {
        return $this->client->request(
            'POST',
            "{$this->base()}/attendance/{$externalEventId}/{$subscriberState}/create",
            ['json' => $body],
        );
    }

    /**
     * Record attendance by contact email.
     *
     * @param  array<string, mixed>  $body
     * @return array<mixed>|object
     */
    public function recordAttendanceByEmail(string $externalEventId, string $subscriberState, array $body): array|object
    {
        return $this->client->request(
            'POST',
            "{$this->base()}/attendance/{$externalEventId}/{$subscriberState}/email-create",
            ['json' => $body],
        );
    }

    // ---------------------------------------------------------------------------
    // Participations
    // ---------------------------------------------------------------------------

    /**
     * @param  array<string, mixed>  $query
     * @return array<mixed>|object
     */
    public function participations(string $marketingEventId, array $query = []): array|object
    {
        return $this->client->request(
            'GET',
            "{$this->base()}/participations/{$marketingEventId}",
            ['query' => $this->filterNull($query)],
        );
    }

    /**
     * @param  array<string, mixed>  $query
     * @return array<mixed>|object
     */
    public function participationsBreakdown(string $marketingEventId, array $query = []): array|object
    {
        return $this->client->request(
            'GET',
            "{$this->base()}/participations/{$marketingEventId}/breakdown",
            ['query' => $this->filterNull($query)],
        );
    }

    // ---------------------------------------------------------------------------
    // App settings
    // ---------------------------------------------------------------------------

    /** @return array<mixed>|object */
    public function getSettings(string $appId): array|object
    {
        return $this->client->request('GET', "{$this->base()}/{$appId}/settings");
    }

    /**
     * @param  array<string, mixed>  $body
     * @return array<mixed>|object
     */
    public function updateSettings(string $appId, array $body): array|object
    {
        return $this->client->request('POST', "{$this->base()}/{$appId}/settings", ['json' => $body]);
    }
}
