<?php

declare(strict_types=1);

namespace HubSpot\Resources;

use HubSpot\Pagination\Paginator;

/**
 * Base for resources that expose HubSpot's standard object CRUD at {base} and
 * {base}/{id}. A concrete resource sets base() and inherits list/all/get/
 * create/update/archive, adding only its own extra endpoints. Resources whose
 * CRUD differs in any way (non-standard id param, extra fixed query, missing a
 * verb, a different path shape) extend Resource directly instead.
 */
abstract class CrudResource extends Resource
{
    abstract protected function base(): string;

    /**
     * @param  array<string, mixed>  $query
     * @return array<mixed>|object
     */
    public function list(array $query = []): array|object
    {
        return $this->client->request('GET', $this->base(), ['query' => $this->filterNull($query)]);
    }

    /** @param  array<string, mixed>  $query */
    public function all(array $query = []): Paginator
    {
        return Paginator::make(fn (?string $after) => $this->list($query + ['after' => $after]));
    }

    /** @return array<mixed>|object */
    public function get(string $id): array|object
    {
        return $this->client->request('GET', "{$this->base()}/{$id}");
    }

    /**
     * Like get(), but null when the record does not exist.
     *
     * @return array<mixed>|object|null
     */
    public function find(string $id): array|object|null
    {
        return $this->orNull(fn () => $this->get($id));
    }

    /**
     * @param  array<string, mixed>  $body
     * @return array<mixed>|object
     */
    public function create(array $body): array|object
    {
        return $this->client->request('POST', $this->base(), ['json' => $body]);
    }

    /**
     * @param  array<string, mixed>  $body
     * @return array<mixed>|object
     */
    public function update(string $id, array $body): array|object
    {
        return $this->client->request('PATCH', "{$this->base()}/{$id}", ['json' => $body]);
    }

    public function archive(string $id): void
    {
        $this->client->request('DELETE', "{$this->base()}/{$id}");
    }
}
