<?php

declare(strict_types=1);

namespace HubSpot\Resources;

use HubSpot\Client;

/**
 * Workflows (Flows) API, reached via $client->automation()->flows(). Versions
 * independently as 2027-03-beta, so it does not follow the client-wide default.
 * The v4 path prefix also applies to explicit version overrides.
 */
final class Flows extends Resource
{
    public const VERSION = '2027-03-beta';

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
        return $this->client->request('GET', "/automation/v4/{$this->version()}/flows", [
            'query' => $this->filterNull($query),
        ]);
    }

    /** @return array<mixed>|object */
    public function get(string $flowId): array|object
    {
        return $this->client->request('GET', "/automation/v4/{$this->version()}/flows/{$flowId}");
    }

    /**
     * @param  list<mixed>  $inputs
     * @return array<mixed>|object
     */
    public function batchRead(array $inputs): array|object
    {
        return $this->batch("/automation/v4/{$this->version()}/flows/batch/read", $inputs);
    }

    /** @return array<mixed>|object */
    public function actionTypes(): array|object
    {
        return $this->client->request('GET', "/automation/v4/{$this->version()}/action-types");
    }
}
