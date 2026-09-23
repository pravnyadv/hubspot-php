<?php

declare(strict_types=1);

namespace HubSpot\Resources;

use HubSpot\Pagination\Paginator;

final class PaymentLinks extends Resource
{
    private function base(): string
    {
        return "/commerce/payment-links/{$this->version()}/payment-links";
    }

    /**
     * @param  array<string, mixed>  $query
     * @return array<mixed>|object
     */
    public function list(array $query = []): array|object
    {
        return $this->client->request('GET', $this->base(), [
            'query' => $this->filterNull($query),
        ]);
    }

    /**
     * @param  array<string, mixed>  $query
     */
    public function all(array $query = []): Paginator
    {
        return Paginator::make(fn (?string $after) => $this->list(['after' => $after] + $query));
    }

    /** @return array<mixed>|object */
    public function get(string $paymentLinkId): array|object
    {
        return $this->client->request('GET', "{$this->base()}/{$paymentLinkId}");
    }
}
