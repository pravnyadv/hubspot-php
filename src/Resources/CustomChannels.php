<?php

declare(strict_types=1);

namespace HubSpot\Resources;

final class CustomChannels extends CrudResource
{
    protected function base(): string
    {
        return "/conversations/custom-channels/{$this->version()}";
    }

    // archive() is overridden (not inherited) because this endpoint is called via
    // Client::send(), not request(): identical HTTP call, kept as-is to avoid any
    // behavior change from decoding a response body CrudResource would otherwise decode.
    public function archive(string $channelId): void
    {
        $this->client->send('DELETE', "{$this->base()}/{$channelId}");
    }

    // Channel accounts -------------------------------------------------------

    /**
     * @param  array<string, mixed>  $query
     * @return array<mixed>|object
     */
    public function listAccounts(string $channelId, array $query = []): array|object
    {
        return $this->client->request('GET', "{$this->base()}/{$channelId}/channel-accounts", [
            'query' => $this->filterNull($query),
        ]);
    }

    /** @return array<mixed>|object */
    public function getAccount(string $channelId, string $accountId): array|object
    {
        return $this->client->request('GET', "{$this->base()}/{$channelId}/channel-accounts/{$accountId}");
    }

    /**
     * @param  array<string, mixed>  $body
     * @return array<mixed>|object
     */
    public function createAccount(string $channelId, array $body): array|object
    {
        return $this->client->request('POST', "{$this->base()}/{$channelId}/channel-accounts", ['json' => $body]);
    }

    /**
     * @param  array<string, mixed>  $body
     * @return array<mixed>|object
     */
    public function updateAccount(string $channelId, string $accountId, array $body): array|object
    {
        return $this->client->request('PATCH', "{$this->base()}/{$channelId}/channel-accounts/{$accountId}", ['json' => $body]);
    }

    /**
     * Update a channel-account staging token (accountName, deliveryIdentifier, ...)
     * during the connect flow.
     *
     * @param  array<string, mixed>  $body
     * @return array<mixed>|object
     */
    public function updateStagingToken(string $channelId, string $accountToken, array $body): array|object
    {
        return $this->client->request(
            'PATCH',
            "{$this->base()}/{$channelId}/channel-account-staging-tokens/{$accountToken}",
            ['json' => $body],
        );
    }

    // Messages ---------------------------------------------------------------

    /** @return array<mixed>|object */
    public function getMessage(string $channelId, string $messageId): array|object
    {
        return $this->client->request('GET', "{$this->base()}/{$channelId}/messages/{$messageId}");
    }

    /**
     * @param  array<string, mixed>  $body
     * @return array<mixed>|object
     */
    public function sendMessage(string $channelId, array $body): array|object
    {
        return $this->client->request('POST', "{$this->base()}/{$channelId}/messages", ['json' => $body]);
    }

    /**
     * Update a message's status (e.g. mark a send as failed with an error).
     *
     * @param  array<string, mixed>  $body
     * @return array<mixed>|object
     */
    public function updateMessage(string $channelId, string $messageId, array $body): array|object
    {
        return $this->client->request('PATCH', "{$this->base()}/{$channelId}/messages/{$messageId}", ['json' => $body]);
    }
}
