<?php

declare(strict_types=1);

namespace HubSpot\Resources;

use HubSpot\Pagination\Paginator;

final class CmsPages extends Resource
{
    private function base(): string
    {
        return "/cms/pages/{$this->version()}";
    }

    /** @return array<mixed>|object */
    public function sitePages(int $limit = 100, ?string $after = null, bool $archived = false): array|object
    {
        return $this->client->request('GET', "{$this->base()}/site-pages", [
            'query' => $this->filterNull([
                'limit' => $limit,
                'after' => $after,
                'archived' => $archived,
            ]),
        ]);
    }

    /** @return array<mixed>|object */
    public function landingPages(int $limit = 100, ?string $after = null, bool $archived = false): array|object
    {
        return $this->client->request('GET', "{$this->base()}/landing-pages", [
            'query' => $this->filterNull([
                'limit' => $limit,
                'after' => $after,
                'archived' => $archived,
            ]),
        ]);
    }

    public function allSitePages(int $limit = 100): Paginator
    {
        return Paginator::make(fn (?string $after) => $this->sitePages($limit, $after));
    }

    public function allLandingPages(int $limit = 100): Paginator
    {
        return Paginator::make(fn (?string $after) => $this->landingPages($limit, $after));
    }
}
