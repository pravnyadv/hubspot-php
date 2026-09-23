<?php

declare(strict_types=1);

namespace HubSpot\Resources;

use HubSpot\Pagination\Paginator;

final class Hubdb extends CrudResource
{
    protected function base(): string
    {
        return "/cms/hubdb/{$this->version()}/tables";
    }

    private function tablePath(string $tableIdOrName, string $suffix = ''): string
    {
        return "{$this->base()}/{$tableIdOrName}{$suffix}";
    }

    // get() is not overridden: CrudResource::get() already builds "{base}/{id}",
    // byte-identical to tablePath($tableIdOrName) with no suffix.

    // archive() is overridden (not inherited) because this endpoint is called via
    // Client::send(), not request(): identical HTTP call, kept as-is to avoid any
    // behavior change from decoding a response body CrudResource would otherwise decode.
    public function archive(string $tableIdOrName): void
    {
        $this->client->send('DELETE', $this->tablePath($tableIdOrName));
    }

    // update() is overridden: "{base}/{id}" is GET/DELETE-only in the spec.
    // Table edits go through the draft, so this delegates to updateDraft().
    /**
     * @param  array<string, mixed>  $body
     * @return array<mixed>|object
     */
    public function update(string $tableIdOrName, array $body): array|object
    {
        return $this->updateDraft($tableIdOrName, $body);
    }

    // --- Tables (draft) ---

    /** @return array<mixed>|object */
    public function getDraft(string $tableIdOrName): array|object
    {
        return $this->client->request('GET', $this->tablePath($tableIdOrName, '/draft'));
    }

    /** @param  array<string, mixed>  $body
     * @return array<mixed>|object
     */
    public function updateDraft(string $tableIdOrName, array $body): array|object
    {
        return $this->client->request('PATCH', $this->tablePath($tableIdOrName, '/draft'), [
            'json' => $body,
        ]);
    }

    /**
     * Publishes the draft table; returns the published table.
     *
     * @return array<mixed>|object
     */
    public function publishDraft(string $tableIdOrName): array|object
    {
        return $this->client->request('POST', $this->tablePath($tableIdOrName, '/draft/publish'));
    }

    /**
     * Resets the draft to the published state.
     *
     * @return array<mixed>|object
     */
    public function resetDraft(string $tableIdOrName): array|object
    {
        return $this->client->request('POST', $this->tablePath($tableIdOrName, '/draft/reset'));
    }

    /**
     * Unpublishes a table (moves it back to draft-only).
     *
     * @return array<mixed>|object
     */
    public function unpublish(string $tableIdOrName): array|object
    {
        return $this->client->request('POST', $this->tablePath($tableIdOrName, '/unpublish'));
    }

    // --- Rows (published) ---

    /** @param  array<string, mixed>  $query
     * @return array<mixed>|object
     */
    public function listRows(string $tableIdOrName, array $query = []): array|object
    {
        return $this->client->request('GET', $this->tablePath($tableIdOrName, '/rows'), [
            'query' => $this->filterNull($query),
        ]);
    }

    /** @param  array<string, mixed>  $query */
    public function allRows(string $tableIdOrName, array $query = []): Paginator
    {
        return Paginator::make(fn (?string $after) => $this->listRows($tableIdOrName, $query + ['after' => $after]));
    }

    /** @return array<mixed>|object */
    public function getRow(string $tableIdOrName, string $rowId): array|object
    {
        return $this->client->request('GET', $this->tablePath($tableIdOrName, "/rows/{$rowId}"));
    }

    /** @param  array<string, mixed>  $body
     * @return array<mixed>|object
     */
    public function createRow(string $tableIdOrName, array $body): array|object
    {
        return $this->client->request('POST', $this->tablePath($tableIdOrName, '/rows'), [
            'json' => $body,
        ]);
    }

    // updateRow() delegates to updateDraftRow(): "{base}/rows/{rowId}" is
    // GET-only in the spec, edits go through the draft row.
    /** @param  array<string, mixed>  $body
     * @return array<mixed>|object
     */
    public function updateRow(string $tableIdOrName, string $rowId, array $body): array|object
    {
        return $this->updateDraftRow($tableIdOrName, $rowId, $body);
    }

    // "{base}/rows/{rowId}" has no DELETE in the spec; archiving a row goes
    // through the draft row (a permanent purge of the draft version).
    public function archiveRow(string $tableIdOrName, string $rowId): void
    {
        $this->client->send('DELETE', $this->tablePath($tableIdOrName, "/rows/{$rowId}/draft"));
    }

    // --- Rows (draft) ---

    /** @param  array<string, mixed>  $query
     * @return array<mixed>|object
     */
    public function listDraftRows(string $tableIdOrName, array $query = []): array|object
    {
        return $this->client->request('GET', $this->tablePath($tableIdOrName, '/rows/draft'), [
            'query' => $this->filterNull($query),
        ]);
    }

    /** @param  array<string, mixed>  $query */
    public function allDraftRows(string $tableIdOrName, array $query = []): Paginator
    {
        return Paginator::make(fn (?string $after) => $this->listDraftRows($tableIdOrName, $query + ['after' => $after]));
    }

    // No createDraftRow(): "{base}/rows/draft" is GET-only in the spec. Draft
    // rows can only be created in bulk, via batchCreateDraftRows() below.

    /** @param  array<string, mixed>  $body
     * @return array<mixed>|object
     */
    public function updateDraftRow(string $tableIdOrName, string $rowId, array $body): array|object
    {
        return $this->client->request('PATCH', $this->tablePath($tableIdOrName, "/rows/{$rowId}/draft"), [
            'json' => $body,
        ]);
    }

    // --- Batch rows (draft) ---

    /** @param  list<mixed>  $inputs
     * @return array<mixed>|object
     */
    public function batchReadRows(string $tableIdOrName, array $inputs): array|object
    {
        return $this->batch($this->tablePath($tableIdOrName, '/rows/batch/read'), $inputs);
    }

    /** @param  list<mixed>  $inputs
     * @return array<mixed>|object
     */
    public function batchCreateDraftRows(string $tableIdOrName, array $inputs): array|object
    {
        return $this->batch($this->tablePath($tableIdOrName, '/rows/draft/batch/create'), $inputs);
    }

    /** @param  list<mixed>  $inputs
     * @return array<mixed>|object
     */
    public function batchUpdateDraftRows(string $tableIdOrName, array $inputs): array|object
    {
        return $this->batch($this->tablePath($tableIdOrName, '/rows/draft/batch/update'), $inputs);
    }

    /** @param  list<mixed>  $inputs */
    public function batchPurgeDraftRows(string $tableIdOrName, array $inputs): void
    {
        $this->client->send('POST', $this->tablePath($tableIdOrName, '/rows/draft/batch/purge'), [
            'json' => ['inputs' => $inputs],
        ]);
    }
}
