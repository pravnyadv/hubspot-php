<?php

declare(strict_types=1);

namespace HubSpot\Resources;

final class EventCompletions extends Resource
{
    /**
     * Send a single behavioral event completion.
     *
     * @param  array<string, mixed>  $body
     * @return array<mixed>|object
     */
    public function send(array $body): array|object
    {
        return $this->client->request('POST', "/events/{$this->version()}/send", [
            'json' => $body,
        ]);
    }

    /**
     * Send multiple event completions in one request.
     *
     * @param  list<array<string, mixed>>  $inputs
     * @return array<mixed>|object
     */
    public function batchSend(array $inputs): array|object
    {
        return $this->batch("/events/{$this->version()}/send/batch", $inputs);
    }
}
