<?php

declare(strict_types=1);

namespace HubSpot\Resources;

final class Associations extends Resource
{
    /**
     * @param  list<mixed>  $inputs
     * @return array<mixed>|object
     */
    public function batchRead(string $fromType, string $toType, array $inputs): array|object
    {
        return $this->batch("/crm/associations/{$this->version()}/{$fromType}/{$toType}/batch/read", $inputs);
    }

    /**
     * @param  list<mixed>  $inputs
     * @return array<mixed>|object
     */
    public function batchCreate(string $fromType, string $toType, array $inputs): array|object
    {
        return $this->batch("/crm/associations/{$this->version()}/{$fromType}/{$toType}/batch/create", $inputs);
    }

    /**
     * @param  list<mixed>  $inputs
     * @return array<mixed>|object
     */
    public function batchArchive(string $fromType, string $toType, array $inputs): array|object
    {
        return $this->batch("/crm/associations/{$this->version()}/{$fromType}/{$toType}/batch/archive", $inputs);
    }

    /** @return array<mixed>|object */
    public function labels(string $fromType, string $toType): array|object
    {
        return $this->client->request('GET', "/crm/associations/{$this->version()}/{$fromType}/{$toType}/labels");
    }

    // Path is under /crm/objects per HubSpot spec, not /crm/associations.
    /** @return array<mixed>|object */
    public function associateDefault(string $fromType, string $fromId, string $toType, string $toId): array|object
    {
        return $this->client->request('PUT', "/crm/objects/{$this->version()}/{$fromType}/{$fromId}/associations/default/{$toType}/{$toId}");
    }
}
