<?php

declare(strict_types=1);

namespace HubSpot\Api;

use HubSpot\Client;
use HubSpot\Resources\BlogAuthors;
use HubSpot\Resources\BlogPosts;
use HubSpot\Resources\BlogSettings;
use HubSpot\Resources\BlogTags;
use HubSpot\Resources\CmsPages;
use HubSpot\Resources\Domains;
use HubSpot\Resources\Hubdb;
use HubSpot\Resources\SourceCode;
use HubSpot\Resources\UrlRedirects;

/**
 * CMS resource group: $client->cms()->pages(), ->sourceCode(), ->blogPosts(),
 * ->hubdb(), etc. Each factory takes an optional per-resource version override
 * (null uses the client default).
 */
final class CmsApi
{
    public function __construct(private readonly Client $client) {}

    public function pages(?string $version = null): CmsPages
    {
        return new CmsPages($this->client, $version);
    }

    public function sourceCode(?string $version = null): SourceCode
    {
        return new SourceCode($this->client, $version);
    }

    public function blogPosts(?string $version = null): BlogPosts
    {
        return new BlogPosts($this->client, $version);
    }

    public function blogAuthors(?string $version = null): BlogAuthors
    {
        return new BlogAuthors($this->client, $version);
    }

    public function blogSettings(?string $version = null): BlogSettings
    {
        return new BlogSettings($this->client, $version);
    }

    public function blogTags(?string $version = null): BlogTags
    {
        return new BlogTags($this->client, $version);
    }

    public function hubdb(?string $version = null): Hubdb
    {
        return new Hubdb($this->client, $version);
    }

    public function domains(?string $version = null): Domains
    {
        return new Domains($this->client, $version);
    }

    public function urlRedirects(?string $version = null): UrlRedirects
    {
        return new UrlRedirects($this->client, $version);
    }
}
