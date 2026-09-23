<?php

declare(strict_types=1);

namespace HubSpot\Resources;

final class Pipelines extends Resource
{
    private function base(): string
    {
        return "/crm/pipelines/{$this->version()}";
    }

    /** @return array<mixed>|object */
    public function all(string $objectType): array|object
    {
        return $this->client->request('GET', "{$this->base()}/{$objectType}");
    }

    /** @return array<mixed>|object */
    public function get(string $objectType, string $pipelineId): array|object
    {
        return $this->client->request('GET', "{$this->base()}/{$objectType}/{$pipelineId}");
    }

    /** @return array<mixed>|object */
    public function stages(string $objectType, string $pipelineId): array|object
    {
        return $this->client->request('GET', "{$this->base()}/{$objectType}/{$pipelineId}/stages");
    }

    /** @return array<mixed>|object */
    public function stage(string $objectType, string $pipelineId, string $stageId): array|object
    {
        return $this->client->request('GET', "{$this->base()}/{$objectType}/{$pipelineId}/stages/{$stageId}");
    }
}
