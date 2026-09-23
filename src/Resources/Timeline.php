<?php

declare(strict_types=1);

namespace HubSpot\Resources;

use GuzzleHttp\Promise\PromiseInterface;

final class Timeline extends Resource
{
    private function base(): string
    {
        return "/integrators/timeline/{$this->version()}";
    }

    /**
     * @param  array<string, mixed>  $event
     * @return array<mixed>|object
     */
    public function createEvent(array $event): array|object
    {
        return $this->client->request('POST', "{$this->base()}/events", [
            'json' => $event,
        ]);
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<mixed>|object
     */
    public function createBatch(array $payload): array|object
    {
        return $this->client->request('POST', "{$this->base()}/events/batch", [
            'json' => $payload,
        ]);
    }

    /**
     * Async batch create, for firing many batches concurrently. Collect the
     * promises and settle them, e.g.
     *   $waves = array_map(fn ($p) => $client->crm()->timeline()->createBatchAsync($p), $payloads);
     *   $results = GuzzleHttp\Promise\Utils::settle($waves)->wait();
     *
     * @param  array<string, mixed>  $payload
     */
    public function createBatchAsync(array $payload): PromiseInterface
    {
        return $this->client->requestAsync('POST', "{$this->base()}/events/batch", [
            'json' => $payload,
        ]);
    }

    // No eventTypes(): "{base}/types/projects" is POST-only (it creates an
    // event-type template); there is no GET listing of event types anywhere
    // in the spec.
}
