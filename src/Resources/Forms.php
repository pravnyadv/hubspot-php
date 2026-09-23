<?php

declare(strict_types=1);

namespace HubSpot\Resources;

use HubSpot\Client;
use HubSpot\Pagination\Paginator;

/**
 * Marketing Forms API. Versions independently as 2026-09-beta; a caller may
 * pass an explicit override (e.g. an older pin) to the factory, and that wins.
 */
final class Forms extends Resource
{
    public const VERSION = '2026-09-beta';

    public function __construct(Client $client, ?string $version = null)
    {
        parent::__construct($client, $version ?? self::VERSION);
    }

    private function base(): string
    {
        return "/marketing/forms/{$this->version()}";
    }

    /** @return array<mixed>|object */
    public function list(int $limit = 100, ?string $after = null): array|object
    {
        return $this->client->request('GET', $this->base(), [
            'query' => $this->filterNull(['limit' => $limit, 'after' => $after]),
        ]);
    }

    /** @return array<mixed>|object */
    public function get(string $formId): array|object
    {
        return $this->client->request('GET', "{$this->base()}/{$formId}");
    }

    public function all(int $limit = 100): Paginator
    {
        return Paginator::make(fn (?string $after) => $this->list($limit, $after));
    }
}
