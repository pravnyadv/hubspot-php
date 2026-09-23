<?php

declare(strict_types=1);

namespace HubSpot\Resources;

use HubSpot\Pagination\Paginator;

final class Lists extends Resource
{
    /**
     * @param  list<string>  $additionalProperties
     * @return array<mixed>|object
     */
    public function search(string $query, int $count = 100, array $additionalProperties = []): array|object
    {
        return $this->client->request('POST', "/crm/lists/{$this->version()}/search", [
            'json' => $this->filterNull([
                'query' => $query,
                'count' => $count,
                'additionalProperties' => $additionalProperties ?: null,
            ]),
        ]);
    }

    /** @return array<mixed>|object */
    public function get(string $listId): array|object
    {
        return $this->client->request('GET', "/crm/lists/{$this->version()}/{$listId}");
    }

    /** @return array<mixed>|object */
    public function memberships(string $listId, int $limit = 100, ?string $after = null): array|object
    {
        return $this->client->request('GET', "/crm/lists/{$this->version()}/{$listId}/memberships", [
            'query' => $this->filterNull(['limit' => $limit, 'after' => $after]),
        ]);
    }

    /** @return array<mixed>|object */
    public function membershipsByJoinOrder(string $listId, int $limit = 250, ?string $after = null): array|object
    {
        return $this->client->request('GET', "/crm/lists/{$this->version()}/{$listId}/memberships/join-order", [
            'query' => $this->filterNull(['limit' => $limit, 'after' => $after]),
        ]);
    }

    public function allMemberships(string $listId, int $limit = 100): Paginator
    {
        return Paginator::make(fn (?string $after) => $this->memberships($listId, $limit, $after));
    }

    public function allMembershipsByJoinOrder(string $listId, int $limit = 250): Paginator
    {
        return Paginator::make(fn (?string $after) => $this->membershipsByJoinOrder($listId, $limit, $after));
    }

    /**
     * @param  array<string, mixed>  $body  a ListCreateRequest (name, objectTypeId, processingType, ...)
     * @return array<mixed>|object
     */
    public function create(array $body): array|object
    {
        return $this->client->request('POST', "/crm/lists/{$this->version()}", ['json' => $body]);
    }

    /**
     * @param  list<int|string>  $recordIds
     * @return array<mixed>|object
     */
    public function addMembers(string $listId, array $recordIds): array|object
    {
        return $this->client->request('PUT', "/crm/lists/{$this->version()}/{$listId}/memberships/add", [
            'json' => $recordIds,
        ]);
    }

    /**
     * @param  list<int|string>  $recordIds
     * @return array<mixed>|object
     */
    public function removeMembers(string $listId, array $recordIds): array|object
    {
        return $this->client->request('PUT', "/crm/lists/{$this->version()}/{$listId}/memberships/remove", [
            'json' => $recordIds,
        ]);
    }

    /**
     * @param  list<int|string>  $recordIdsToAdd
     * @param  list<int|string>  $recordIdsToRemove
     * @return array<mixed>|object
     */
    public function addAndRemoveMembers(string $listId, array $recordIdsToAdd, array $recordIdsToRemove): array|object
    {
        return $this->client->request('PUT', "/crm/lists/{$this->version()}/{$listId}/memberships/add-and-remove", [
            'json' => ['recordIdsToAdd' => $recordIdsToAdd, 'recordIdsToRemove' => $recordIdsToRemove],
        ]);
    }

    /** @return array<mixed>|object */
    public function addMembersFromList(string $listId, string $sourceListId): array|object
    {
        return $this->client->request('PUT', "/crm/lists/{$this->version()}/{$listId}/memberships/add-from/{$sourceListId}");
    }

    public function removeAllMembers(string $listId): void
    {
        $this->client->request('DELETE', "/crm/lists/{$this->version()}/{$listId}/memberships");
    }
}
