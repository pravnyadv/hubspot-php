<?php

declare(strict_types=1);

namespace HubSpot\Resources;

final class Conversations extends Resource
{
    private function base(): string
    {
        return "/conversations/conversations/{$this->version()}";
    }

    /**
     * @param  array<string, mixed>  $query
     * @return array<mixed>|object
     */
    public function threads(array $query = []): array|object
    {
        return $this->client->request('GET', "{$this->base()}/threads", [
            'query' => $this->filterNull($query),
        ]);
    }

    /**
     * @param  array<string, mixed>  $query  e.g. ['association' => 'TICKET']
     * @return array<mixed>|object
     */
    public function thread(string $threadId, array $query = []): array|object
    {
        return $this->client->request('GET', "{$this->base()}/threads/{$threadId}", [
            'query' => $this->filterNull($query),
        ]);
    }

    /**
     * @param  array<string, mixed>  $query
     * @return array<mixed>|object
     */
    public function messages(string $threadId, array $query = []): array|object
    {
        return $this->client->request('GET', "{$this->base()}/threads/{$threadId}/messages", [
            'query' => $this->filterNull($query),
        ]);
    }

    /**
     * @param  array<string, mixed>  $message
     * @return array<mixed>|object
     */
    public function sendMessage(string $threadId, array $message): array|object
    {
        return $this->client->request('POST', "{$this->base()}/threads/{$threadId}/messages", [
            'json' => $message,
        ]);
    }

    /** @return array<mixed>|object */
    public function inboxes(): array|object
    {
        return $this->client->request('GET', "{$this->base()}/inboxes");
    }

    /** @return array<mixed>|object */
    public function getInbox(string $inboxId): array|object
    {
        return $this->client->request('GET', "{$this->base()}/inboxes/{$inboxId}");
    }

    /** @return array<mixed>|object */
    public function channels(): array|object
    {
        return $this->client->request('GET', "{$this->base()}/channels");
    }

    /** @return array<mixed>|object */
    public function channelAccounts(): array|object
    {
        return $this->client->request('GET', "{$this->base()}/channel-accounts");
    }
}
