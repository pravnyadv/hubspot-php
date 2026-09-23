<?php

declare(strict_types=1);

namespace HubSpot\Resources;

use HubSpot\Pagination\Paginator;

final class Users extends Resource
{
    private function base(): string
    {
        return "/crm/objects/{$this->version()}/users";
    }

    /**
     * List CRM user objects with optional query params.
     *
     * @param  array<string, mixed>  $query
     * @return array<mixed>|object
     */
    public function list(array $query = []): array|object
    {
        return $this->client->request('GET', $this->base(), [
            'query' => $this->filterNull($query),
        ]);
    }

    /**
     * Paginate all CRM users, yielding each user record.
     *
     * @param  array<string, mixed>  $query
     */
    public function all(array $query = []): Paginator
    {
        return Paginator::make(fn (?string $after) => $this->list(array_merge($query, ['after' => $after])));
    }

    /**
     * Retrieve a single CRM user by ID.
     *
     * @return array<mixed>|object
     */
    public function get(string $userId): array|object
    {
        return $this->client->request('GET', "{$this->base()}/{$userId}");
    }

    /**
     * Search CRM users by filter criteria.
     *
     * @param  array<string, mixed>  $request
     * @return array<mixed>|object
     */
    public function search(array $request): array|object
    {
        return $this->client->request('POST', "{$this->base()}/search", [
            'json' => $request,
        ]);
    }

    /**
     * Batch-create CRM users.
     *
     * @param  list<array<string, mixed>>  $inputs
     * @return array<mixed>|object
     */
    public function batchCreate(array $inputs): array|object
    {
        return $this->batch("{$this->base()}/batch/create", $inputs);
    }

    /**
     * Batch-read CRM users by ID or unique property.
     *
     * @param  list<array<string, mixed>>  $inputs
     * @return array<mixed>|object
     */
    public function batchRead(array $inputs): array|object
    {
        return $this->batch("{$this->base()}/batch/read", $inputs);
    }

    /**
     * Batch-update CRM users.
     *
     * @param  list<array<string, mixed>>  $inputs
     * @return array<mixed>|object
     */
    public function batchUpdate(array $inputs): array|object
    {
        return $this->batch("{$this->base()}/batch/update", $inputs);
    }

    /**
     * Batch-upsert CRM users (create or update by unique ID property).
     *
     * @param  list<array<string, mixed>>  $inputs
     * @return array<mixed>|object
     */
    public function batchUpsert(array $inputs): array|object
    {
        return $this->batch("{$this->base()}/batch/upsert", $inputs);
    }

    /**
     * Batch-archive (soft-delete) CRM users by ID.
     *
     * @param  list<array<string, mixed>>  $inputs
     */
    public function batchArchive(array $inputs): void
    {
        $this->batch("{$this->base()}/batch/archive", $inputs);
    }

    /**
     * Merge two CRM user records into one.
     *
     * @param  array<string, mixed>  $body
     * @return array<mixed>|object
     */
    public function merge(array $body): array|object
    {
        return $this->client->request('POST', "{$this->base()}/merge", [
            'json' => $body,
        ]);
    }

    /**
     * Permanently delete a CRM user and purge their data (GDPR).
     *
     * @param  array<string, mixed>  $body
     */
    public function gdprDelete(array $body): void
    {
        $this->client->request('POST', "{$this->base()}/gdpr-delete", [
            'json' => $body,
        ]);
    }

    /**
     * List associations for a user to another object type.
     *
     * @param  array<string, mixed>  $query
     * @return array<mixed>|object
     */
    public function associations(string $userId, string $toObjectType, array $query = []): array|object
    {
        return $this->client->request(
            'GET',
            "{$this->base()}/{$userId}/associations/{$toObjectType}",
            ['query' => $this->filterNull($query)],
        );
    }
}
