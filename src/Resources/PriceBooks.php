<?php

declare(strict_types=1);

namespace HubSpot\Resources;

use HubSpot\Pagination\Paginator;

final class PriceBooks extends CrudResource
{
    protected function base(): string
    {
        return "/commerce/price-books/{$this->version()}/price-books";
    }

    /** @return array<mixed>|object */
    public function activate(string $priceBookId): array|object
    {
        return $this->client->request('POST', "{$this->base()}/{$priceBookId}/activate");
    }

    /** @return array<mixed>|object */
    public function deactivate(string $priceBookId): array|object
    {
        return $this->client->request('POST', "{$this->base()}/{$priceBookId}/deactivate");
    }

    /**
     * @param  array<string, mixed>  $body
     * @return array<mixed>|object
     */
    public function validate(string $priceBookId, array $body = []): array|object
    {
        return $this->client->request('POST', "{$this->base()}/{$priceBookId}/validate", ['json' => $body]);
    }

    // Items

    /**
     * @param  array<string, mixed>  $query
     * @return array<mixed>|object
     */
    public function listItems(string $priceBookId, array $query = []): array|object
    {
        return $this->client->request('GET', "{$this->base()}/{$priceBookId}/items", [
            'query' => $this->filterNull($query),
        ]);
    }

    /**
     * @param  array<string, mixed>  $query
     */
    public function allItems(string $priceBookId, array $query = []): Paginator
    {
        return Paginator::make(fn (?string $after) => $this->listItems($priceBookId, ['after' => $after] + $query));
    }

    /**
     * @param  array<string, mixed>  $body
     * @return array<mixed>|object
     */
    public function createItem(string $priceBookId, array $body): array|object
    {
        return $this->client->request('POST', "{$this->base()}/{$priceBookId}/items", ['json' => $body]);
    }

    /** @return array<mixed>|object */
    public function getItem(string $priceBookId, string $priceBookItemId): array|object
    {
        return $this->client->request('GET', "{$this->base()}/{$priceBookId}/items/{$priceBookItemId}");
    }

    /**
     * @param  array<string, mixed>  $body
     * @return array<mixed>|object
     */
    public function updateItem(string $priceBookId, string $priceBookItemId, array $body): array|object
    {
        return $this->client->request('PATCH', "{$this->base()}/{$priceBookId}/items/{$priceBookItemId}", ['json' => $body]);
    }

    public function archiveItem(string $priceBookId, string $priceBookItemId): void
    {
        $this->client->request('DELETE', "{$this->base()}/{$priceBookId}/items/{$priceBookItemId}");
    }

    /**
     * @param  list<array<string, mixed>>  $inputs
     * @return array<mixed>|object
     */
    public function batchCreateItems(string $priceBookId, array $inputs): array|object
    {
        return $this->batch("{$this->base()}/{$priceBookId}/items/batch/create", $inputs);
    }

    /**
     * @param  list<array<string, mixed>>  $inputs
     * @return array<mixed>|object
     */
    public function batchUpdateItems(string $priceBookId, array $inputs): array|object
    {
        return $this->batch("{$this->base()}/{$priceBookId}/items/batch/update", $inputs);
    }

    /**
     * @param  list<array<string, mixed>>  $inputs
     * @return array<mixed>|object
     */
    public function batchArchiveItems(string $priceBookId, array $inputs): array|object
    {
        return $this->batch("{$this->base()}/{$priceBookId}/items/batch/archive", $inputs);
    }
}
