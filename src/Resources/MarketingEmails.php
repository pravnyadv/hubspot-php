<?php

declare(strict_types=1);

namespace HubSpot\Resources;

final class MarketingEmails extends CrudResource
{
    protected function base(): string
    {
        return "/marketing/emails/{$this->version()}";
    }

    /**
     * Clone an existing email; pass at least the new name in $body.
     *
     * @param  array<string, mixed>  $body
     * @return array<mixed>|object
     */
    public function clone_(array $body): array|object
    {
        return $this->client->request('POST', "{$this->base()}/clone", ['json' => $body]);
    }

    /** @return array<mixed>|object */
    public function publish(string $emailId): array|object
    {
        return $this->client->request('POST', "{$this->base()}/{$emailId}/publish");
    }

    /** @return array<mixed>|object */
    public function unpublish(string $emailId): array|object
    {
        return $this->client->request('POST', "{$this->base()}/{$emailId}/unpublish");
    }

    /** @return array<mixed>|object */
    public function getDraft(string $emailId): array|object
    {
        return $this->client->request('GET', "{$this->base()}/{$emailId}/draft");
    }

    /**
     * Reset the draft to match the currently published version.
     *
     * @return array<mixed>|object
     */
    public function resetDraft(string $emailId): array|object
    {
        return $this->client->request('POST', "{$this->base()}/{$emailId}/draft/reset");
    }

    /**
     * @param  array<string, mixed>  $query
     * @return array<mixed>|object
     */
    public function revisions(string $emailId, array $query = []): array|object
    {
        return $this->client->request('GET', "{$this->base()}/{$emailId}/revisions", [
            'query' => $this->filterNull($query),
        ]);
    }

    /** @return array<mixed>|object */
    public function revision(string $emailId, string $revisionId): array|object
    {
        return $this->client->request('GET', "{$this->base()}/{$emailId}/revisions/{$revisionId}");
    }

    /**
     * Restore a revision to the live published version.
     *
     * @return array<mixed>|object
     */
    public function restoreRevision(string $emailId, string $revisionId): array|object
    {
        return $this->client->request('POST', "{$this->base()}/{$emailId}/revisions/{$revisionId}/restore");
    }

    /**
     * Restore a revision into the draft without publishing.
     *
     * @return array<mixed>|object
     */
    public function restoreRevisionToDraft(string $emailId, string $revisionId): array|object
    {
        return $this->client->request('POST', "{$this->base()}/{$emailId}/revisions/{$revisionId}/restore-to-draft");
    }

    /**
     * @param  array<string, mixed>  $query
     * @return array<mixed>|object
     */
    public function statisticsList(array $query = []): array|object
    {
        return $this->client->request('GET', "{$this->base()}/statistics/list", [
            'query' => $this->filterNull($query),
        ]);
    }

    /**
     * @param  array<string, mixed>  $query
     * @return array<mixed>|object
     */
    public function statisticsHistogram(array $query = []): array|object
    {
        return $this->client->request('GET', "{$this->base()}/statistics/histogram", [
            'query' => $this->filterNull($query),
        ]);
    }

    /**
     * Create an A/B test variation for an existing email.
     *
     * @param  array<string, mixed>  $body
     * @return array<mixed>|object
     */
    public function createAbVariation(array $body): array|object
    {
        return $this->client->request('POST', "{$this->base()}/ab-test/create-variation", ['json' => $body]);
    }

    /** @return array<mixed>|object */
    public function getAbVariation(string $emailId): array|object
    {
        return $this->client->request('GET', "{$this->base()}/{$emailId}/ab-test/get-variation");
    }
}
