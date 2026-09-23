<?php

declare(strict_types=1);

namespace HubSpot\Resources;

final class WebhookSubscriptions extends Resource
{
    private function base(int $appId): string
    {
        return "/app-webhooks/{$this->version()}/{$appId}";
    }

    // -------------------------------------------------------------------------
    // Settings
    // -------------------------------------------------------------------------

    /** @return array<mixed>|object */
    public function getSettings(int $appId): array|object
    {
        return $this->client->request('GET', "{$this->base($appId)}/settings");
    }

    /**
     * @param  array<string, mixed>  $body
     * @return array<mixed>|object
     */
    public function updateSettings(int $appId, array $body): array|object
    {
        return $this->client->request('PUT', "{$this->base($appId)}/settings", [
            'json' => $body,
        ]);
    }

    // -------------------------------------------------------------------------
    // Subscriptions
    // -------------------------------------------------------------------------

    /**
     * @param  array<string, mixed>  $query
     * @return array<mixed>|object
     */
    public function list(int $appId, array $query = []): array|object
    {
        return $this->client->request('GET', "{$this->base($appId)}/subscriptions", [
            'query' => $this->filterNull($query),
        ]);
    }

    /** @return array<mixed>|object */
    public function get(int $appId, string $subscriptionId): array|object
    {
        return $this->client->request('GET', "{$this->base($appId)}/subscriptions/{$subscriptionId}");
    }

    /**
     * @param  array<string, mixed>  $body
     * @return array<mixed>|object
     */
    public function create(int $appId, array $body): array|object
    {
        return $this->client->request('POST', "{$this->base($appId)}/subscriptions", [
            'json' => $body,
        ]);
    }

    /**
     * @param  array<string, mixed>  $body
     * @return array<mixed>|object
     */
    public function update(int $appId, string $subscriptionId, array $body): array|object
    {
        return $this->client->request('PATCH', "{$this->base($appId)}/subscriptions/{$subscriptionId}", [
            'json' => $body,
        ]);
    }

    public function archive(int $appId, string $subscriptionId): void
    {
        $this->client->send('DELETE', "{$this->base($appId)}/subscriptions/{$subscriptionId}");
    }

    /**
     * Batch-update subscription states in one call.
     *
     * @param  list<array<string, mixed>>  $inputs
     * @return array<mixed>|object
     */
    public function batchUpdate(int $appId, array $inputs): array|object
    {
        return $this->batch("{$this->base($appId)}/subscriptions/batch/update", $inputs);
    }
}
