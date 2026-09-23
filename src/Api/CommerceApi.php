<?php

declare(strict_types=1);

namespace HubSpot\Api;

use HubSpot\Client;
use HubSpot\Resources\PaymentLinks;
use HubSpot\Resources\PriceBooks;

/**
 * Commerce resource group: $client->commerce()->paymentLinks(), ->priceBooks().
 * CRM commerce objects (orders, invoices, quotes, line items, carts) are
 * reached through $client->crm()->objects($type) instead.
 */
final class CommerceApi
{
    public function __construct(private readonly Client $client) {}

    public function paymentLinks(?string $version = null): PaymentLinks
    {
        return new PaymentLinks($this->client, $version);
    }

    public function priceBooks(?string $version = null): PriceBooks
    {
        return new PriceBooks($this->client, $version);
    }
}
