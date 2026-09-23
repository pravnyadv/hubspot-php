<?php

declare(strict_types=1);

namespace HubSpot\Api;

use HubSpot\Client;
use HubSpot\Resources\AutomationActions;
use HubSpot\Resources\Flows;
use HubSpot\Resources\Sequences;

/**
 * Automation resource group: $client->automation()->flows() (workflows),
 * ->sequences(), ->actions() (custom workflow action definitions).
 */
final class AutomationApi
{
    public function __construct(private readonly Client $client) {}

    public function flows(?string $version = null): Flows
    {
        return new Flows($this->client, $version);
    }

    public function sequences(?string $version = null): Sequences
    {
        return new Sequences($this->client, $version);
    }

    public function actions(?string $version = null): AutomationActions
    {
        return new AutomationActions($this->client, $version);
    }
}
