<?php

declare(strict_types=1);

namespace HubSpot\Resources;

final class ObjectTags extends Resource
{
    /**
     * List all tags defined for a given CRM object type.
     *
     * @param  array<string, mixed>  $query
     * @return array<mixed>|object
     */
    public function list(string $objectTypeId, array $query = []): array|object
    {
        return $this->client->request('GET', "/crm/object-tags/{$this->version()}/{$objectTypeId}", [
            'query' => $this->filterNull($query),
        ]);
    }

    /**
     * Retrieve a single tag by its ID within an object type.
     *
     * @return array<mixed>|object
     */
    public function get(string $objectTypeId, string $tagId): array|object
    {
        return $this->client->request('GET', "/crm/object-tags/{$this->version()}/{$objectTypeId}/{$tagId}");
    }

    /**
     * Create a new tag for an object type.
     *
     * @param  array<string, mixed>  $body
     * @return array<mixed>|object
     */
    public function create(string $objectTypeId, array $body): array|object
    {
        return $this->client->request('POST', "/crm/object-tags/{$this->version()}/{$objectTypeId}", [
            'json' => $body,
        ]);
    }

    /**
     * Partially update an existing tag.
     *
     * @param  array<string, mixed>  $body
     * @return array<mixed>|object
     */
    public function update(string $objectTypeId, string $tagId, array $body): array|object
    {
        return $this->client->request('PATCH', "/crm/object-tags/{$this->version()}/{$objectTypeId}/{$tagId}", [
            'json' => $body,
        ]);
    }

    /**
     * Delete a tag from an object type.
     */
    public function delete(string $objectTypeId, string $tagId): void
    {
        $this->client->request('DELETE', "/crm/object-tags/{$this->version()}/{$objectTypeId}/{$tagId}");
    }
}
