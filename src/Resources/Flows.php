<?php

declare(strict_types=1);

namespace HubSpot\Resources;

use HubSpot\Client;

/**
 * Workflows (Flows) API, reached via $client->automation()->flows(). Versions
 * independently as 2026-09-beta, so it does not follow the client-wide default.
 */
final class Flows extends Resource
{
    public const VERSION = '2026-09-beta';

    public function __construct(Client $client, ?string $version = null)
    {
        parent::__construct($client, $version ?? self::VERSION);
    }

    /**
     * @param  array<string, mixed>  $query
     * @return array<mixed>|object
     */
    public function list(array $query = []): array|object
    {
        return $this->client->request('GET', "/automation/{$this->version()}/flows", [
            'query' => $this->filterNull($query),
        ]);
    }

    /** @return array<mixed>|object */
    public function get(string $flowId): array|object
    {
        return $this->client->request('GET', "/automation/{$this->version()}/flows/{$flowId}");
    }

    /**
     * @param  list<mixed>  $inputs
     * @return array<mixed>|object
     */
    public function batchRead(array $inputs): array|object
    {
        return $this->batch("/automation/{$this->version()}/flows/batch/read", $inputs);
    }

    /** @return array<mixed>|object */
    public function actionTypes(): array|object
    {
        return $this->client->request('GET', "/automation/{$this->version()}/action-types");
    }
}
