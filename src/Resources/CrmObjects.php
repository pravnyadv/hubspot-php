<?php

declare(strict_types=1);

namespace HubSpot\Resources;

use HubSpot\Client;

final class CrmObjects extends Resource
{
    public function __construct(
        Client $client,
        private readonly string $objectType,
        ?string $version = null,
    ) {
        parent::__construct($client, $version);
    }

    private function base(): string
    {
        return "/crm/objects/{$this->version()}/{$this->objectType}";
    }

    /**
     * $idProperty reads by a unique property instead of the record id, e.g.
     * get('jane@example.com', idProperty: 'email'), without using a search call.
     *
     * @param  list<string>  $properties
     * @param  list<string>  $propertiesWithHistory
     * @param  list<string>  $associations  object types whose associated ids to include
     * @return array<mixed>|object
     */
    public function get(
        string $id,
        array $properties = [],
        array $propertiesWithHistory = [],
        array $associations = [],
        ?string $idProperty = null,
    ): array|object {
        return $this->client->request('GET', "{$this->base()}/".rawurlencode($id), [
            'query' => $this->filterNull([
                'properties' => $properties ? implode(',', $properties) : null,
                'propertiesWithHistory' => $propertiesWithHistory ? implode(',', $propertiesWithHistory) : null,
                'associations' => $associations ? implode(',', $associations) : null,
                'idProperty' => $idProperty,
            ]),
        ]);
    }

    /**
     * Like get(), but null when the record does not exist.
     *
     * @param  list<string>  $properties
     * @param  list<string>  $propertiesWithHistory
     * @param  list<string>  $associations
     * @return array<mixed>|object|null
     */
    public function find(
        string $id,
        array $properties = [],
        array $propertiesWithHistory = [],
        array $associations = [],
        ?string $idProperty = null,
    ): array|object|null {
        return $this->orNull(fn () => $this->get($id, $properties, $propertiesWithHistory, $associations, $idProperty));
    }

    /**
     * @param  array<string, mixed>  $properties
     * @return array<mixed>|object
     */
    public function create(array $properties): array|object
    {
        return $this->client->request('POST', $this->base(), [
            'json' => ['properties' => $properties],
        ]);
    }

    /**
     * @param  array<string, mixed>  $properties
     * @return array<mixed>|object
     */
    public function update(string $id, array $properties): array|object
    {
        return $this->client->request('PATCH', "{$this->base()}/{$id}", [
            'json' => ['properties' => $properties],
        ]);
    }

    public function archive(string $id): void
    {
        $this->client->request('DELETE', "{$this->base()}/{$id}");
    }

    /**
     * Accepts a raw PublicObjectSearchRequest (filterGroups, properties, limit, query, sorts, after).
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
     * @param  list<array<string, mixed>>  $inputs
     * @return array<mixed>|object
     */
    public function batchCreate(array $inputs): array|object
    {
        return $this->batch("{$this->base()}/batch/create", $inputs);
    }

    /**
     * @param  list<array<string, mixed>>  $inputs
     * @return array<mixed>|object
     */
    public function batchRead(array $inputs): array|object
    {
        return $this->batch("{$this->base()}/batch/read", $inputs);
    }

    /**
     * @param  list<array<string, mixed>>  $inputs
     * @return array<mixed>|object
     */
    public function batchUpdate(array $inputs): array|object
    {
        return $this->batch("{$this->base()}/batch/update", $inputs);
    }

    /**
     * @param  list<array<string, mixed>>  $inputs
     * @return array<mixed>|object
     */
    public function batchUpsert(array $inputs): array|object
    {
        return $this->batch("{$this->base()}/batch/upsert", $inputs);
    }

    /** @param  list<array<string, mixed>>  $inputs */
    public function batchArchive(array $inputs): void
    {
        $this->batch("{$this->base()}/batch/archive", $inputs);
    }

    /**
     * @param  array<string, mixed>  $body  {primaryObjectId, objectIdToMerge}
     * @return array<mixed>|object
     */
    public function merge(array $body): array|object
    {
        return $this->client->request('POST', "{$this->base()}/merge", ['json' => $body]);
    }

    /** @param  array<string, mixed>  $body  {objectId, idProperty?} */
    public function gdprDelete(array $body): void
    {
        $this->client->request('POST', "{$this->base()}/gdpr-delete", ['json' => $body]);
    }
}
