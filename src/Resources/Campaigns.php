<?php

declare(strict_types=1);

namespace HubSpot\Resources;

use HubSpot\Pagination\Paginator;

final class Campaigns extends Resource
{
    private function base(): string
    {
        return "/marketing/campaigns/{$this->version()}";
    }

    // ---------------------------------------------------------------------------
    // Collection + single
    // ---------------------------------------------------------------------------

    /**
     * @param  array<string, mixed>  $query
     * @return array<mixed>|object
     */
    public function list(array $query = []): array|object
    {
        return $this->client->request('GET', $this->base(), [
            'query' => $this->filterNull($query),
        ]);
    }

    /** @param  array<string, mixed>  $query */
    public function all(array $query = []): Paginator
    {
        return Paginator::make(fn (?string $after) => $this->list($query + ['after' => $after]));
    }

    /** @return array<mixed>|object */
    public function get(string $campaignGuid): array|object
    {
        return $this->client->request('GET', "{$this->base()}/{$campaignGuid}");
    }

    // ---------------------------------------------------------------------------
    // Batch operations
    // ---------------------------------------------------------------------------

    /**
     * @param  list<array<string, mixed>>  $inputs
     * @return array<mixed>|object
     */
    public function batchCreate(array $inputs): array|object
    {
        return $this->batch("{$this->base()}/batch/create", $inputs);
    }

    /**
     * @param  list<array<string, mixed>>  $inputs
     * @return array<mixed>|object
     */
    public function batchRead(array $inputs): array|object
    {
        return $this->batch("{$this->base()}/batch/read", $inputs);
    }

    /**
     * @param  list<array<string, mixed>>  $inputs
     * @return array<mixed>|object
     */
    public function batchUpdate(array $inputs): array|object
    {
        return $this->batch("{$this->base()}/batch/update", $inputs);
    }

    /**
     * @param  list<array<string, mixed>>  $inputs
     * @return array<mixed>|object
     */
    public function batchArchive(array $inputs): array|object
    {
        return $this->batch("{$this->base()}/batch/archive", $inputs);
    }

    // ---------------------------------------------------------------------------
    // Clone
    // ---------------------------------------------------------------------------

    /**
     * @param  array<string, mixed>  $body
     * @return array<mixed>|object
     */
    public function duplicate(array $body): array|object
    {
        return $this->client->request('POST', "{$this->base()}/clone", ['json' => $body]);
    }

    /** @return array<mixed>|object */
    public function cloneStatus(string $campaignGuid): array|object
    {
        return $this->client->request('GET', "{$this->base()}/clone/{$campaignGuid}/status");
    }

    // ---------------------------------------------------------------------------
    // Asset types
    // ---------------------------------------------------------------------------

    /** @return array<mixed>|object */
    public function assetTypes(): array|object
    {
        return $this->client->request('GET', "{$this->base()}/asset-types");
    }

    /** @return array<mixed>|object */
    public function cloneAssetTypes(): array|object
    {
        return $this->client->request('GET', "{$this->base()}/clone/asset-types");
    }

    // ---------------------------------------------------------------------------
    // Campaign assets
    // ---------------------------------------------------------------------------

    /**
     * @param  array<string, mixed>  $query
     * @return array<mixed>|object
     */
    public function assets(string $campaignGuid, string $assetType, array $query = []): array|object
    {
        return $this->client->request(
            'GET',
            "{$this->base()}/{$campaignGuid}/assets/{$assetType}",
            ['query' => $this->filterNull($query)],
        );
    }

    /** Add an asset to a campaign. */
    public function addAsset(string $campaignGuid, string $assetType, string $assetId): void
    {
        $this->client->request('PUT', "{$this->base()}/{$campaignGuid}/assets/{$assetType}/{$assetId}");
    }

    /** Remove an asset from a campaign. */
    public function removeAsset(string $campaignGuid, string $assetType, string $assetId): void
    {
        $this->client->request('DELETE', "{$this->base()}/{$campaignGuid}/assets/{$assetType}/{$assetId}");
    }

    // ---------------------------------------------------------------------------
    // Budget
    // ---------------------------------------------------------------------------

    /**
     * Retrieve a single budget item ("{campaignGuid}/budget" itself is POST-only, for adding one).
     *
     * @return array<mixed>|object
     */
    public function budget(string $campaignGuid, string $budgetId): array|object
    {
        return $this->client->request('GET', "{$this->base()}/{$campaignGuid}/budget/{$budgetId}");
    }

    /** @return array<mixed>|object */
    public function budgetTotals(string $campaignGuid): array|object
    {
        return $this->client->request('GET', "{$this->base()}/{$campaignGuid}/budget/totals");
    }

    /**
     * @param  array<string, mixed>  $body
     * @return array<mixed>|object
     */
    public function updateBudget(string $campaignGuid, string $budgetId, array $body): array|object
    {
        return $this->client->request(
            'PUT',
            "{$this->base()}/{$campaignGuid}/budget/{$budgetId}",
            ['json' => $body],
        );
    }

    public function deleteBudget(string $campaignGuid, string $budgetId): void
    {
        $this->client->request('DELETE', "{$this->base()}/{$campaignGuid}/budget/{$budgetId}");
    }

    // ---------------------------------------------------------------------------
    // Reporting
    // ---------------------------------------------------------------------------

    /** @return array<mixed>|object */
    public function metrics(string $campaignGuid): array|object
    {
        return $this->client->request('GET', "{$this->base()}/{$campaignGuid}/reports/metrics");
    }

    /** @return array<mixed>|object */
    public function contactReport(string $campaignGuid, string $contactType): array|object
    {
        return $this->client->request(
            'GET',
            "{$this->base()}/{$campaignGuid}/reports/contacts/{$contactType}",
        );
    }

    /** @return array<mixed>|object */
    public function revenue(string $campaignGuid): array|object
    {
        return $this->client->request('GET', "{$this->base()}/{$campaignGuid}/reports/revenue");
    }

    // ---------------------------------------------------------------------------
    // Spend
    // ---------------------------------------------------------------------------

    /**
     * Retrieve a single spend item ("{campaignGuid}/spend" itself is POST-only, for adding one).
     *
     * @return array<mixed>|object
     */
    public function spend(string $campaignGuid, string $spendId): array|object
    {
        return $this->client->request('GET', "{$this->base()}/{$campaignGuid}/spend/{$spendId}");
    }
}
