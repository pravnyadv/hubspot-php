<?php

declare(strict_types=1);

namespace HubSpot\Resources;

use HubSpot\Pagination\Paginator;

final class Imports extends Resource
{
    /**
     * List all imports, optionally filtered by query params.
     *
     * @param  array<string, mixed>  $query
     * @return array<mixed>|object
     */
    public function list(array $query = []): array|object
    {
        return $this->client->request('GET', "/crm/imports/{$this->version()}", [
            'query' => $this->filterNull($query),
        ]);
    }

    /**
     * Paginate all imports, yielding each import record.
     *
     * @param  array<string, mixed>  $query
     */
    public function all(array $query = []): Paginator
    {
        return Paginator::make(fn (?string $after) => $this->list(array_merge($query, ['after' => $after])));
    }

    /**
     * Retrieve a single import by its ID.
     *
     * @return array<mixed>|object
     */
    public function get(string $importId): array|object
    {
        return $this->client->request('GET', "/crm/imports/{$this->version()}/{$importId}");
    }

    /**
     * Start a new import. The importRequest JSON is sent as a multipart part;
     * an optional CSV/spreadsheet file is the second part when provided.
     *
     * @param  array<string, mixed>  $importRequest
     * @return array<mixed>|object
     */
    public function create(array $importRequest, ?string $fileContent = null, ?string $filename = null): array|object
    {
        $parts = [
            [
                'name' => 'importRequest',
                'contents' => (string) json_encode($importRequest),
                'headers' => ['Content-Type' => 'application/json'],
            ],
        ];

        if ($fileContent !== null) {
            $parts[] = [
                'name' => 'files',
                'contents' => $fileContent,
                'filename' => $filename ?? 'import.csv',
            ];
        }

        return $this->client->request('POST', "/crm/imports/{$this->version()}", [
            'multipart' => $parts,
        ]);
    }

    /**
     * Cancel an in-progress import.
     *
     * @return array<mixed>|object
     */
    public function cancel(string $importId): array|object
    {
        return $this->client->request('POST', "/crm/imports/{$this->version()}/{$importId}/cancel");
    }

    /**
     * Retrieve row-level errors for a completed or failed import.
     *
     * @param  array<string, mixed>  $query
     * @return array<mixed>|object
     */
    public function errors(string $importId, array $query = []): array|object
    {
        return $this->client->request('GET', "/crm/imports/{$this->version()}/{$importId}/errors", [
            'query' => $this->filterNull($query),
        ]);
    }
}
