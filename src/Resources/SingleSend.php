<?php

declare(strict_types=1);

namespace HubSpot\Resources;

final class SingleSend extends Resource
{
    /**
     * Send a single marketing email to one recipient.
     *
     * @param  array<string, mixed>  $request
     * @return array<mixed>|object
     */
    public function send(array $request): array|object
    {
        return $this->client->request(
            'POST',
            "/marketing/email-campaigns/{$this->version()}/single-send",
            ['json' => $request],
        );
    }
}
