<?php

declare(strict_types=1);

namespace HubSpot\Api;

use HubSpot\Client;
use HubSpot\Resources\Associations;
use HubSpot\Resources\CrmObjects;
use HubSpot\Resources\Exports;
use HubSpot\Resources\Imports;
use HubSpot\Resources\Lists;
use HubSpot\Resources\ObjectTags;
use HubSpot\Resources\Owners;
use HubSpot\Resources\Pipelines;
use HubSpot\Resources\Properties;
use HubSpot\Resources\Schemas;
use HubSpot\Resources\Timeline;
use HubSpot\Resources\Users;

/**
 * CRM resource group: $client->crm()->objects('contacts'), ->lists(), etc.
 * Each factory takes an optional per-resource version override (null uses the
 * client default), so one family can be pinned to an older dated version.
 */
final class CrmApi
{
    public function __construct(private readonly Client $client) {}

    public function objects(string $objectType, ?string $version = null): CrmObjects
    {
        return new CrmObjects($this->client, $objectType, $version);
    }

    public function lists(?string $version = null): Lists
    {
        return new Lists($this->client, $version);
    }

    public function owners(?string $version = null): Owners
    {
        return new Owners($this->client, $version);
    }

    public function properties(?string $version = null): Properties
    {
        return new Properties($this->client, $version);
    }

    public function associations(?string $version = null): Associations
    {
        return new Associations($this->client, $version);
    }

    public function pipelines(?string $version = null): Pipelines
    {
        return new Pipelines($this->client, $version);
    }

    public function schemas(?string $version = null): Schemas
    {
        return new Schemas($this->client, $version);
    }

    public function timeline(?string $version = null): Timeline
    {
        return new Timeline($this->client, $version);
    }

    public function imports(?string $version = null): Imports
    {
        return new Imports($this->client, $version);
    }

    public function exports(?string $version = null): Exports
    {
        return new Exports($this->client, $version);
    }

    public function users(?string $version = null): Users
    {
        return new Users($this->client, $version);
    }

    public function objectTags(?string $version = null): ObjectTags
    {
        return new ObjectTags($this->client, $version);
    }

    // Typed aliases for standard CRM object types; each delegates to the generic
    // objects() resource, so a caller can write $client->crm()->contacts() as
    // well as $client->crm()->objects('contacts'). Any other object type
    // (including custom p_* objects) is reached through objects($type).

    public function contacts(?string $version = null): CrmObjects
    {
        return $this->objects('contacts', $version);
    }

    public function companies(?string $version = null): CrmObjects
    {
        return $this->objects('companies', $version);
    }

    public function deals(?string $version = null): CrmObjects
    {
        return $this->objects('deals', $version);
    }

    public function tickets(?string $version = null): CrmObjects
    {
        return $this->objects('tickets', $version);
    }

    public function leads(?string $version = null): CrmObjects
    {
        return $this->objects('leads', $version);
    }

    public function calls(?string $version = null): CrmObjects
    {
        return $this->objects('calls', $version);
    }

    public function emails(?string $version = null): CrmObjects
    {
        return $this->objects('emails', $version);
    }

    public function meetings(?string $version = null): CrmObjects
    {
        return $this->objects('meetings', $version);
    }

    public function notes(?string $version = null): CrmObjects
    {
        return $this->objects('notes', $version);
    }

    public function tasks(?string $version = null): CrmObjects
    {
        return $this->objects('tasks', $version);
    }

    public function products(?string $version = null): CrmObjects
    {
        return $this->objects('products', $version);
    }

    public function lineItems(?string $version = null): CrmObjects
    {
        return $this->objects('line_items', $version);
    }

    public function quotes(?string $version = null): CrmObjects
    {
        return $this->objects('quotes', $version);
    }
}
