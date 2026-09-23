<?php

declare(strict_types=1);

namespace HubSpot\Resources;

final class UrlRedirects extends CrudResource
{
    // "{v}/url-mappings" is a legacy alias (shared with the 2026-03 spec, item
    // GET/DELETE only, no PATCH). The current, fully-specced CRUD surface is
    // plain "{v}" for list/create and "{v}/{urlRedirectId}" for get/patch/delete.
    protected function base(): string
    {
        return "/cms/url-redirects/{$this->version()}";
    }

    // archive() is overridden (not inherited) because this endpoint is called via
    // Client::send(), not request(): identical HTTP call, kept as-is to avoid any
    // behavior change from decoding a response body CrudResource would otherwise decode.
    public function archive(string $id): void
    {
        $this->client->send('DELETE', "{$this->base()}/{$id}");
    }
}
