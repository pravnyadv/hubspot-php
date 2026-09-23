<?php

declare(strict_types=1);

namespace HubSpot\Resources;

final class Exports extends Resource
{
    /**
     * Start an asynchronous CRM export. Returns a task descriptor with a taskId
     * that you pass to taskStatus() to poll for completion.
     *
     * @param  array<string, mixed>  $body
     * @return array<mixed>|object
     */
    public function startAsync(array $body): array|object
    {
        return $this->client->request('POST', "/crm/exports/{$this->version()}/export/async", [
            'json' => $body,
        ]);
    }

    /**
     * Poll the status of an async export task started with startAsync().
     *
     * @return array<mixed>|object
     */
    public function taskStatus(string $taskId): array|object
    {
        return $this->client->request('GET', "/crm/exports/{$this->version()}/export/async/tasks/{$taskId}/status");
    }

    /**
     * Retrieve metadata for a completed export by its export ID.
     *
     * @return array<mixed>|object
     */
    public function get(string $exportId): array|object
    {
        return $this->client->request('GET', "/crm/exports/{$this->version()}/export/{$exportId}");
    }
}
