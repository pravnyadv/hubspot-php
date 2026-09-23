<?php

declare(strict_types=1);

namespace HubSpot\Resources;

use HubSpot\Pagination\Paginator;

final class UserProvisioning extends Resource
{
    private function base(): string
    {
        return "/settings/users/{$this->version()}";
    }

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

    public function all(): Paginator
    {
        return Paginator::make(fn (?string $after) => $this->list(['after' => $after]));
    }

    /** @return array<mixed>|object */
    public function get(string $userId): array|object
    {
        return $this->client->request('GET', "{$this->base()}/{$userId}");
    }

    /** @return array<mixed>|object */
    public function roles(): array|object
    {
        return $this->client->request('GET', "{$this->base()}/roles");
    }

    /** @return array<mixed>|object */
    public function seats(): array|object
    {
        return $this->client->request('GET', "{$this->base()}/seats");
    }

    /** @return array<mixed>|object */
    public function teams(): array|object
    {
        return $this->client->request('GET', "{$this->base()}/teams");
    }
}
