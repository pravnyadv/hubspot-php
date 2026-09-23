<?php

declare(strict_types=1);

namespace HubSpot\Resources;

final class VisitorIdentification extends Resource
{
    /**
     * Create a signed identification token for the Conversations visitor
     * identification API. Pass the contact email and (optionally) first/last
     * name so HubSpot can associate the visitor with a known contact.
     *
     * @param  array<string, mixed>  $body  Must include at minimum { email: string }.
     * @return array<mixed>|object
     */
    public function createToken(array $body): array|object
    {
        return $this->client->request(
            'POST',
            "/visitor-identification/{$this->version()}/tokens/create",
            ['json' => $body],
        );
    }
}
