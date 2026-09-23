<?php

declare(strict_types=1);

namespace HubSpot\Resources;

use HubSpot\Pagination\Paginator;

final class Teams extends Resource
{
    private function base(): string
    {
        return "/settings/teams/{$this->version()}";
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
    public function get(string $teamId): array|object
    {
        return $this->client->request('GET', "{$this->base()}/{$teamId}");
    }

    /**
     * @param  array<string, mixed>  $query
     * @return array<mixed>|object
     */
    public function members(string $teamId, array $query = []): array|object
    {
        return $this->client->request('GET', "{$this->base()}/{$teamId}/members", [
            'query' => $this->filterNull($query),
        ]);
    }

    /**
     * Batch-update team membership. Each entry in $inputs is a member descriptor
     * (userId, role, etc.) as HubSpot defines for the batch endpoint.
     *
     * @param  list<mixed>  $inputs
     * @return array<mixed>|object
     */
    public function batchMembers(string $teamId, array $inputs): array|object
    {
        return $this->batch("{$this->base()}/{$teamId}/members/batch", $inputs);
    }

    public function removeMember(string $teamId, string $userId): void
    {
        $this->client->send('DELETE', "{$this->base()}/{$teamId}/members/{$userId}");
    }
}
