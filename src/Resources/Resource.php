<?php

declare(strict_types=1);

namespace HubSpot\Resources;

use HubSpot\Client;

/**
 * Base for every resource. Holds the Client and resolves the date-based version
 * a resource stamps into its own paths ("/crm/owners/{$this->version()}").
 *
 * Version resolution, most specific wins: a per-resource override passed to the
 * factory (e.g. $client->lists('2026-03')) beats the client-wide default. This
 * lets a caller pin one API family to an older dated version when HubSpot has
 * not shipped the new one for that path yet, while everything else stays
 * current. A family that versions independently (Forms) passes its own default
 * as the override.
 */
abstract class Resource
{
    public function __construct(
        protected readonly Client $client,
        private readonly ?string $version = null,
    ) {}

    protected function version(): string
    {
        return $this->version ?? $this->client->version();
    }

    /**
     * Drop null values so optional query/body params are omitted rather than
     * sent as empty. Keeps 0 and '' and false, which are meaningful values.
     *
     * @param  array<string, mixed>  $params
     * @return array<string, mixed>
     */
    protected function filterNull(array $params): array
    {
        return array_filter($params, static fn ($value): bool => $value !== null);
    }

    /**
     * POST a HubSpot batch operation body of {"inputs": [...]}.
     *
     * @param  list<mixed>  $inputs
     * @return array<mixed>|object
     */
    protected function batch(string $path, array $inputs): array|object
    {
        return $this->client->request('POST', $path, ['json' => ['inputs' => $inputs]]);
    }
}
