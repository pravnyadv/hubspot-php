<?php

declare(strict_types=1);

namespace HubSpot\Resources;

final class BlogPosts extends CrudResource
{
    protected function base(): string
    {
        return "/cms/blogs/{$this->version()}/posts";
    }

    /** @param  list<mixed>  $inputs */
    public function batchArchive(array $inputs): void
    {
        $this->batch("{$this->base()}/batch/archive", $inputs);
    }

    /**
     * @param  list<mixed>  $inputs
     * @return array<mixed>|object
     */
    public function batchCreate(array $inputs): array|object
    {
        return $this->batch("{$this->base()}/batch/create", $inputs);
    }

    /**
     * @param  list<mixed>  $inputs
     * @return array<mixed>|object
     */
    public function batchRead(array $inputs): array|object
    {
        return $this->batch("{$this->base()}/batch/read", $inputs);
    }

    /**
     * @param  list<mixed>  $inputs
     * @return array<mixed>|object
     */
    public function batchUpdate(array $inputs): array|object
    {
        return $this->batch("{$this->base()}/batch/update", $inputs);
    }

    /**
     * Clone a post with the given properties.
     *
     * @param  array<string, mixed>  $body
     * @return array<mixed>|object
     */
    public function duplicate(array $body): array|object
    {
        return $this->client->request('POST', "{$this->base()}/clone", ['json' => $body]);
    }

    /** @param  array<string, mixed>  $body */
    public function schedule(array $body): void
    {
        $this->client->request('POST', "{$this->base()}/schedule", ['json' => $body]);
    }

    /** @return array<mixed>|object */
    public function getDraft(string $id): array|object
    {
        return $this->client->request('GET', "{$this->base()}/{$id}/draft");
    }

    /**
     * @param  array<string, mixed>  $body
     * @return array<mixed>|object
     */
    public function updateDraft(string $id, array $body): array|object
    {
        return $this->client->request('PATCH', "{$this->base()}/{$id}/draft", ['json' => $body]);
    }

    public function pushLive(string $id): void
    {
        $this->client->request('POST', "{$this->base()}/{$id}/draft/push-live");
    }

    public function resetDraft(string $id): void
    {
        $this->client->request('POST', "{$this->base()}/{$id}/draft/reset");
    }

    /**
     * @param  array<string, mixed>  $query
     * @return array<mixed>|object
     */
    public function revisions(string $id, array $query = []): array|object
    {
        return $this->client->request('GET', "{$this->base()}/{$id}/revisions", [
            'query' => $this->filterNull($query),
        ]);
    }

    /** @return array<mixed>|object */
    public function revision(string $id, string $revisionId): array|object
    {
        return $this->client->request('GET', "{$this->base()}/{$id}/revisions/{$revisionId}");
    }

    /** @return array<mixed>|object */
    public function restoreRevision(string $id, string $revisionId): array|object
    {
        return $this->client->request('POST', "{$this->base()}/{$id}/revisions/{$revisionId}/restore");
    }

    /** @return array<mixed>|object */
    public function restoreRevisionToDraft(string $id, string $revisionId): array|object
    {
        return $this->client->request('POST', "{$this->base()}/{$id}/revisions/{$revisionId}/restore-to-draft");
    }
}
