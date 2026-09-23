<?php

declare(strict_types=1);

namespace HubSpot\Api;

use HubSpot\Client;
use HubSpot\Resources\Campaigns;
use HubSpot\Resources\CommunicationPreferences;
use HubSpot\Resources\Forms;
use HubSpot\Resources\MarketingEmails;
use HubSpot\Resources\MarketingEvents;
use HubSpot\Resources\SingleSend;
use HubSpot\Resources\Transactional;

/**
 * Marketing resource group: $client->marketing()->forms(), ->emails(),
 * ->events(), ->campaigns(), etc. Forms defaults to its own independent version
 * (2026-09-beta); an explicit override still wins.
 */
final class MarketingApi
{
    public function __construct(private readonly Client $client) {}

    public function forms(?string $version = null): Forms
    {
        return new Forms($this->client, $version);
    }

    public function emails(?string $version = null): MarketingEmails
    {
        return new MarketingEmails($this->client, $version);
    }

    public function events(?string $version = null): MarketingEvents
    {
        return new MarketingEvents($this->client, $version);
    }

    public function campaigns(?string $version = null): Campaigns
    {
        return new Campaigns($this->client, $version);
    }

    public function singleSend(?string $version = null): SingleSend
    {
        return new SingleSend($this->client, $version);
    }

    public function transactional(?string $version = null): Transactional
    {
        return new Transactional($this->client, $version);
    }

    public function subscriptions(?string $version = null): CommunicationPreferences
    {
        return new CommunicationPreferences($this->client, $version);
    }
}
