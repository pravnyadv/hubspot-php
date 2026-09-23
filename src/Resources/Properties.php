<?php

declare(strict_types=1);

namespace HubSpot\Resources;

final class Properties extends Resource
{
    /** @return array<mixed>|object */
    public function all(string $objectType): array|object
    {
        return $this->client->request('GET', "/crm/properties/{$this->version()}/{$objectType}");
    }

    /** @return array<mixed>|object */
    public function get(string $objectType, string $propertyName): array|object
    {
        return $this->client->request('GET', "/crm/properties/{$this->version()}/{$objectType}/{$propertyName}");
    }

    /**
     * Like get(), but null when the property does not exist.
     *
     * @return array<mixed>|object|null
     */
    public function find(string $objectType, string $propertyName): array|object|null
    {
        return $this->orNull(fn () => $this->get($objectType, $propertyName));
    }

    /**
     * @param  array<string, mixed>  $property
     * @return array<mixed>|object
     */
    public function create(string $objectType, array $property): array|object
    {
        return $this->client->request('POST', "/crm/properties/{$this->version()}/{$objectType}", [
            'json' => $property,
        ]);
    }

    public function archive(string $objectType, string $propertyName): void
    {
        $this->client->request('DELETE', "/crm/properties/{$this->version()}/{$objectType}/{$propertyName}");
    }

    /**
     * @param  list<array<string, mixed>>  $inputs
     * @return array<mixed>|object
     */
    public function batchCreate(string $objectType, array $inputs): array|object
    {
        return $this->batch("/crm/properties/{$this->version()}/{$objectType}/batch/create", $inputs);
    }

    /**
     * @param  list<array<string, mixed>>  $inputs
     * @return array<mixed>|object
     */
    public function batchRead(string $objectType, array $inputs): array|object
    {
        return $this->batch("/crm/properties/{$this->version()}/{$objectType}/batch/read", $inputs);
    }

    /** @param  list<array<string, mixed>>  $inputs */
    public function batchArchive(string $objectType, array $inputs): void
    {
        $this->batch("/crm/properties/{$this->version()}/{$objectType}/batch/archive", $inputs);
    }
}
