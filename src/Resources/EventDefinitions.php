<?php

declare(strict_types=1);

namespace HubSpot\Resources;

final class EventDefinitions extends CrudResource
{
    protected function base(): string
    {
        return "/events/{$this->version()}/event-definitions";
    }

    // archive() is overridden (not inherited) because this endpoint is called via
    // Client::send(), not request(): identical HTTP call, kept as-is to avoid any
    // behavior change from decoding a response body CrudResource would otherwise decode.
    public function archive(string $eventName): void
    {
        $this->client->send('DELETE', "{$this->base()}/{$eventName}");
    }

    /**
     * Add a property to a custom event definition.
     *
     * @param  array<string, mixed>  $body
     * @return array<mixed>|object
     */
    public function createProperty(string $eventName, array $body): array|object
    {
        return $this->client->request('POST', "{$this->base()}/{$eventName}/property", [
            'json' => $body,
        ]);
    }

    // No getProperty(): "{eventName}/property/{propertyName}" is patch/delete-only
    // in the spec, no GET endpoint exists to read a single property definition.

    /**
     * @param  array<string, mixed>  $body
     * @return array<mixed>|object
     */
    public function updateProperty(string $eventName, string $propertyName, array $body): array|object
    {
        return $this->client->request('PATCH', "{$this->base()}/{$eventName}/property/{$propertyName}", [
            'json' => $body,
        ]);
    }

    public function archiveProperty(string $eventName, string $propertyName): void
    {
        $this->client->send('DELETE', "{$this->base()}/{$eventName}/property/{$propertyName}");
    }
}
