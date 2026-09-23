<?php

declare(strict_types=1);

namespace HubSpot\Resources;

final class Transactional extends Resource
{
    private function base(): string
    {
        return "/marketing/transactional/{$this->version()}";
    }

    /**
     * Send a transactional single email.
     *
     * @param  array<string, mixed>  $request
     * @return array<mixed>|object
     */
    public function send(array $request): array|object
    {
        return $this->client->request('POST', "{$this->base()}/single-email/send", ['json' => $request]);
    }

    /**
     * @param  array<string, mixed>  $query
     * @return array<mixed>|object
     */
    public function listSmtpTokens(array $query = []): array|object
    {
        return $this->client->request('GET', "{$this->base()}/smtp-tokens", [
            'query' => $this->filterNull($query),
        ]);
    }

    /**
     * @param  array<string, mixed>  $body
     * @return array<mixed>|object
     */
    public function createSmtpToken(array $body): array|object
    {
        return $this->client->request('POST', "{$this->base()}/smtp-tokens", ['json' => $body]);
    }

    /** @return array<mixed>|object */
    public function getSmtpToken(string $tokenId): array|object
    {
        return $this->client->request('GET', "{$this->base()}/smtp-tokens/{$tokenId}");
    }

    public function archiveSmtpToken(string $tokenId): void
    {
        $this->client->request('DELETE', "{$this->base()}/smtp-tokens/{$tokenId}");
    }

    /**
     * Reset the password for an SMTP token.
     *
     * @return array<mixed>|object
     */
    public function resetSmtpTokenPassword(string $tokenId): array|object
    {
        return $this->client->request('POST', "{$this->base()}/smtp-tokens/{$tokenId}/password-reset");
    }
}
