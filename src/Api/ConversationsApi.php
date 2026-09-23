<?php

declare(strict_types=1);

namespace HubSpot\Api;

use HubSpot\Client;
use HubSpot\Resources\Conversations;
use HubSpot\Resources\CustomChannels;
use HubSpot\Resources\VisitorIdentification;

/**
 * Conversations resource group: $client->conversations()->inbox() (threads,
 * messages, inboxes, channels), ->customChannels(), ->visitorIdentification().
 */
final class ConversationsApi
{
    public function __construct(private readonly Client $client) {}

    public function inbox(?string $version = null): Conversations
    {
        return new Conversations($this->client, $version);
    }

    public function customChannels(?string $version = null): CustomChannels
    {
        return new CustomChannels($this->client, $version);
    }

    public function visitorIdentification(?string $version = null): VisitorIdentification
    {
        return new VisitorIdentification($this->client, $version);
    }
}
