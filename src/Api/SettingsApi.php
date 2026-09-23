<?php

declare(strict_types=1);

namespace HubSpot\Api;

use HubSpot\Client;
use HubSpot\Resources\BusinessUnits;
use HubSpot\Resources\Teams;
use HubSpot\Resources\UserProvisioning;
use HubSpot\Resources\WebhookSubscriptions;

/**
 * Account settings group: $client->settings()->teams(), ->users() (user
 * provisioning, distinct from the CRM Users read API at $client->crm()->users()),
 * ->businessUnits(), ->webhooks() (app webhook subscription management, distinct
 * from HubSpot::webhooks() which verifies inbound signatures).
 */
final class SettingsApi
{
    public function __construct(private readonly Client $client) {}

    public function teams(?string $version = null): Teams
    {
        return new Teams($this->client, $version);
    }

    public function users(?string $version = null): UserProvisioning
    {
        return new UserProvisioning($this->client, $version);
    }

    public function businessUnits(?string $version = null): BusinessUnits
    {
        return new BusinessUnits($this->client, $version);
    }

    public function webhooks(?string $version = null): WebhookSubscriptions
    {
        return new WebhookSubscriptions($this->client, $version);
    }
}
