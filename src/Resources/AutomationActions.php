<?php

declare(strict_types=1);

namespace HubSpot\Resources;

use HubSpot\Pagination\Paginator;

/**
 * Custom workflow extension actions: define, version, and complete
 * asynchronous action callbacks for app-owned automation steps.
 */
final class AutomationActions extends Resource
{
    private function base(): string
    {
        return "/automation/actions/{$this->version()}";
    }

    // ── Definition collection ────────────────────────────────────────────────

    /**
     * @param  array<string, mixed>  $query
     * @return array<mixed>|object
     */
    public function list(string $appId, array $query = []): array|object
    {
        return $this->client->request('GET', "{$this->base()}/{$appId}", [
            'query' => $this->filterNull($query),
        ]);
    }

    public function all(string $appId): Paginator
    {
        return Paginator::make(fn (?string $after) => $this->list($appId, ['after' => $after]));
    }

    // ── Definition CRUD ──────────────────────────────────────────────────────

    /** @return array<mixed>|object */
    public function get(string $appId, string $definitionId): array|object
    {
        return $this->client->request('GET', "{$this->base()}/{$appId}/{$definitionId}");
    }

    /**
     * @param  array<string, mixed>  $body
     * @return array<mixed>|object
     */
    public function create(string $appId, array $body): array|object
    {
        return $this->client->request('POST', "{$this->base()}/{$appId}", [
            'json' => $body,
        ]);
    }

    /**
     * @param  array<string, mixed>  $body
     * @return array<mixed>|object
     */
    public function update(string $appId, string $definitionId, array $body): array|object
    {
        return $this->client->request('PATCH', "{$this->base()}/{$appId}/{$definitionId}", [
            'json' => $body,
        ]);
    }

    public function archive(string $appId, string $definitionId): void
    {
        $this->client->send('DELETE', "{$this->base()}/{$appId}/{$definitionId}");
    }

    // ── Inline functions ─────────────────────────────────────────────────────

    /** @return array<mixed>|object */
    public function functions(string $appId, string $definitionId): array|object
    {
        return $this->client->request('GET', "{$this->base()}/{$appId}/{$definitionId}/functions");
    }

    /** @return array<mixed>|object */
    public function getFunction(string $appId, string $definitionId, string $functionType): array|object
    {
        return $this->client->request('GET', "{$this->base()}/{$appId}/{$definitionId}/functions/{$functionType}");
    }

    /**
     * Create or replace the function body for the given type (PRE_ACTION_EXECUTION,
     * POST_ACTION_EXECUTION, PRE_FETCH_OPTIONS, POST_FETCH_OPTIONS).
     *
     * @param  array<string, mixed>  $body
     * @return array<mixed>|object
     */
    public function putFunction(string $appId, string $definitionId, string $functionType, array $body): array|object
    {
        return $this->client->request('PUT', "{$this->base()}/{$appId}/{$definitionId}/functions/{$functionType}", [
            'json' => $body,
        ]);
    }

    public function deleteFunction(string $appId, string $definitionId, string $functionType): void
    {
        $this->client->send('DELETE', "{$this->base()}/{$appId}/{$definitionId}/functions/{$functionType}");
    }

    // ── Revisions ────────────────────────────────────────────────────────────

    /** @return array<mixed>|object */
    public function revisions(string $appId, string $definitionId): array|object
    {
        return $this->client->request('GET', "{$this->base()}/{$appId}/{$definitionId}/revisions");
    }

    /** @return array<mixed>|object */
    public function revision(string $appId, string $definitionId, string $revisionId): array|object
    {
        return $this->client->request('GET', "{$this->base()}/{$appId}/{$definitionId}/revisions/{$revisionId}");
    }

    // ── Callbacks ─────────────────────────────────────────────────────────────

    /**
     * Complete a single async action callback. Called by the app after it
     * finishes its side-effect so HubSpot can resume the enrollment.
     *
     * @param  array<string, mixed>  $body
     */
    public function completeCallback(string $callbackId, array $body): void
    {
        $this->client->send('POST', "/automation/actions/callbacks/{$this->version()}/{$callbackId}/complete", [
            'json' => $body,
            'headers' => ['Content-Type' => 'application/json'],
        ]);
    }

    /**
     * Complete multiple async callbacks in one call.
     *
     * @param  list<array<string, mixed>>  $inputs
     */
    public function completeCallbacks(array $inputs): void
    {
        $this->client->send('POST', "/automation/actions/callbacks/{$this->version()}/complete", [
            'json' => ['inputs' => $inputs],
            'headers' => ['Content-Type' => 'application/json'],
        ]);
    }
}
