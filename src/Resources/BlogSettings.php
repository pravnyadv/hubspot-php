<?php

declare(strict_types=1);

namespace HubSpot\Resources;

use HubSpot\Pagination\Paginator;

final class BlogSettings extends Resource
{
    private function base(): string
    {
        // Different top-level segment from blogs/: blog-settings owns its own versioned root.
        return "/cms/blog-settings/{$this->version()}/settings";
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

    /** @param  array<string, mixed>  $query */
    public function all(array $query = []): Paginator
    {
        return Paginator::make(fn (?string $after) => $this->list(['after' => $after] + $query));
    }

    /** @return array<mixed>|object */
    public function get(string $blogId): array|object
    {
        return $this->client->request('GET', "{$this->base()}/{$blogId}");
    }

    // No update(): "{base}/{blogId}" is GET-only in the spec, and blog settings
    // has no PATCH/PUT endpoint anywhere else (the multi-language endpoints
    // operate on language groups, not a single blog's settings).

    /**
     * @param  array<string, mixed>  $query
     * @return array<mixed>|object
     */
    public function revisions(string $blogId, array $query = []): array|object
    {
        return $this->client->request('GET', "{$this->base()}/{$blogId}/revisions", [
            'query' => $this->filterNull($query),
        ]);
    }

    /** @return array<mixed>|object */
    public function revision(string $blogId, string $revisionId): array|object
    {
        return $this->client->request('GET', "{$this->base()}/{$blogId}/revisions/{$revisionId}");
    }
}
