<?php

declare(strict_types=1);

namespace HubSpot\Resources;

final class Schemas extends Resource
{
    private function base(): string
    {
        return "/crm-object-schemas/{$this->version()}/schemas";
    }

    /** @return array<mixed>|object */
    public function all(): array|object
    {
        return $this->client->request('GET', $this->base());
    }

    /** @return array<mixed>|object */
    public function get(string $objectType): array|object
    {
        return $this->client->request('GET', "{$this->base()}/{$objectType}");
    }

    /**
     * @param  array<string, mixed>  $schema
     * @return array<mixed>|object
     */
    public function create(array $schema): array|object
    {
        return $this->client->request('POST', $this->base(), ['json' => $schema]);
    }

    public function archive(string $objectType): void
    {
        $this->client->request('DELETE', "{$this->base()}/{$objectType}");
    }
}
