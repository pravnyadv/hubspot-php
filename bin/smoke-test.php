<?php

declare(strict_types=1);

/**
 * Manual live smoke test against a real HubSpot portal. NOT part of the
 * automated suite (which is fully mocked and needs no network). This confirms
 * the transport, auth, and a handful of read-only date-based endpoints work
 * end to end against HubSpot itself.
 *
 * Read-only: it only GETs/searches, never writes.
 *
 * Usage:  HUBSPOT_TOKEN=pat-na1-... php bin/smoke-test.php
 *     or  php bin/smoke-test.php <access-token>
 */

require __DIR__.'/../vendor/autoload.php';

use HubSpot\Exceptions\HubSpotException;
use HubSpot\HubSpot;

$token = getenv('HUBSPOT_TOKEN') ?: ($argv[1] ?? null);

if ($token === null || $token === '') {
    fwrite(STDERR, "Set HUBSPOT_TOKEN (a private-app or OAuth access token), or pass it as the first argument.\n");
    exit(1);
}

$client = HubSpot::withAccessToken($token);

$failures = 0;

$probe = function (string $label, callable $fn) use (&$failures): void {
    try {
        $result = $fn();
        $count = match (true) {
            is_string($result) => strlen($result).' bytes',
            is_object($result) && isset($result->results) && is_array($result->results) => count($result->results).' item(s)',
            is_array($result) && isset($result['results']) && is_array($result['results']) => count($result['results']).' item(s)',
            default => 'ok',
        };
        echo "  OK    {$label}  ({$count})\n";
    } catch (HubSpotException $e) {
        $failures++;
        echo "  FAIL  {$label}\n        ".$e::class.' ('.$e->getCode().'): '.$e->getMessage()."\n";
    }
};

echo "HubSpot live smoke test (read-only)\n\n";

$probe('account.details          GET  /account-info/2026-09/details', fn () => $client->account()->details());
$probe('crm.owners.page          GET  /crm/owners/2026-09', fn () => $client->crm()->owners()->page(1));
$probe('crm.properties(contacts) GET  /crm/properties/2026-09/contacts', fn () => $client->crm()->properties()->all('contacts'));
$probe('crm.contacts.search      POST /crm/objects/2026-09/contacts/search', fn () => $client->crm()->contacts()->search(['limit' => 1]));
$probe('crm.lists.search         POST /crm/lists/2026-09/search', fn () => $client->crm()->lists()->search('', 1));

echo "\n".($failures === 0 ? "All probes passed.\n" : "{$failures} probe(s) failed.\n");
exit($failures === 0 ? 0 : 1);
