<?php

declare(strict_types=1);

namespace HubSpot\Api;

use HubSpot\Client;
use HubSpot\Resources\EventCompletions;
use HubSpot\Resources\EventDefinitions;
use HubSpot\Resources\Events;

/**
 * Custom behavioral events group: $client->events()->query() to read events,
 * ->definitions() to manage event types, ->completions() to send events.
 */
final class EventsApi
{
    public function __construct(private readonly Client $client) {}

    public function query(?string $version = null): Events
    {
        return new Events($this->client, $version);
    }

    public function definitions(?string $version = null): EventDefinitions
    {
        return new EventDefinitions($this->client, $version);
    }

    public function completions(?string $version = null): EventCompletions
    {
        return new EventCompletions($this->client, $version);
    }
}
