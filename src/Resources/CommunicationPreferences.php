<?php

declare(strict_types=1);

namespace HubSpot\Resources;

final class CommunicationPreferences extends Resource
{
    private function base(): string
    {
        return "/communication-preferences/{$this->version()}";
    }

    /** @return array<mixed>|object */
    public function definitions(): array|object
    {
        return $this->client->request('GET', "{$this->base()}/definitions");
    }

    /** @return array<mixed>|object */
    public function statuses(string $subscriberId): array|object
    {
        return $this->client->request('GET', "{$this->base()}/statuses/{$subscriberId}");
    }

    /**
     * @param  list<array<string, mixed>>  $inputs
     * @return array<mixed>|object
     */
    public function batchWrite(array $inputs): array|object
    {
        return $this->batch("{$this->base()}/statuses/batch/write", $inputs);
    }

    /** @return array<mixed>|object */
    public function unsubscribeAll(string $subscriberId): array|object
    {
        return $this->client->request('POST', "{$this->base()}/statuses/{$subscriberId}/unsubscribe-all");
    }
}
