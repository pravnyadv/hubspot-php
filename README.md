# hubspot-php

[![Tests](https://github.com/pravnyadv/hubspot-php/actions/workflows/tests.yml/badge.svg)](https://github.com/pravnyadv/hubspot-php/actions/workflows/tests.yml)
[![Latest Version](https://img.shields.io/packagist/v/pravnyadv/hubspot-php.svg)](https://packagist.org/packages/pravnyadv/hubspot-php)
[![PHP Version](https://img.shields.io/packagist/php-v/pravnyadv/hubspot-php.svg)](https://packagist.org/packages/pravnyadv/hubspot-php)
[![License](https://img.shields.io/github/license/pravnyadv/hubspot-php.svg)](LICENSE)

A small, framework-free PHP client for HubSpot's date-based API versions (`2026-09` and later). The official SDK still calls the numbered versions HubSpot is retiring in 2027; this maps every resource to its correct dated path, read from HubSpot's own OpenAPI catalog.

Plain `stdClass`/array responses (no model classes), a grouped facade, per-family version pinning, auto-refreshing OAuth, idempotency-aware retries, cursor pagination, typed exceptions, webhook verification, and async with a concurrency pool.

## Install

```bash
composer require pravnyadv/hubspot-php
```

PHP 8.3+, Guzzle 7 or 8.

## Usage

```php
use HubSpot\HubSpot;

$client  = HubSpot::withAccessToken('pat-na1-...');
$contact = $client->crm()->contacts()->get('123', ['email']);
echo $contact->properties->email;
```

Resources are grouped by product area:

```php
$client->crm()->objects('contacts')   // generic, any object type incl. custom p_*
$client->crm()->contacts() / deals() / tickets() / lists() / owners() / properties()
      ->associations() / pipelines() / schemas() / timeline() / imports() / exports() / users()
$client->cms()->pages() / sourceCode() / blogPosts() / hubdb() / domains() / urlRedirects()
$client->marketing()->forms() / emails() / campaigns() / subscriptions()
$client->commerce()->paymentLinks() / priceBooks()
$client->automation()->flows() / sequences() / actions()
$client->conversations()->inbox() / customChannels() / visitorIdentification()
$client->events()  $client->settings()  $client->files()  $client->account()  $client->scheduler()
```

Anything without a typed method is still reachable: `$client->request('GET', '/crm/objects/2026-09/tickets/456')`.

### Pagination

```php
foreach ($client->crm()->owners()->all() as $owner) {   // walks the `after` cursor
    echo $owner->id, PHP_EOL;
}
```

### Arrays instead of objects

```php
use HubSpot\ResponseFormat;

$client = HubSpot::withAccessToken('pat-na1-...', responseFormat: ResponseFormat::Assoc);
// now $contact['properties']['email']
```

### Pin one family to a different version

```php
$client->crm()->lists('2026-03')->get('789');   // just lists; everything else stays on 2026-09
```

### Search builder

```php
use HubSpot\Search\SearchRequest;

$request = SearchRequest::create()
    ->where('email', 'HAS_PROPERTY')
    ->where('lastname', 'EQ', 'Doe')
    ->limit(50);

$client->crm()->contacts()->search($request->toArray());
```

### OAuth

Auto-refreshes; you persist each refreshed token via the callback.

```php
use HubSpot\HubSpot;
use HubSpot\Auth\OAuthAuth;
use HubSpot\Auth\TokenSet;

$oauth  = HubSpot::oauth();
$url    = $oauth->authorizationUrl($clientId, $redirect, ['crm.objects.contacts.read']);
$tokens = $oauth->exchangeAuthorizationCode($clientId, $secret, $_GET['code'], $redirect);

$auth = (new OAuthAuth($oauth, $clientId, $secret, $tokens))
    ->onTokenRefreshed(fn (TokenSet $t) => $db->save($t->accessToken, $t->refreshToken, $t->expiresAt));

$client = HubSpot::withOAuth($auth);
```

### Webhooks

```php
$ok = HubSpot::webhooks($clientSecret)->isValidV3($method, $url, $rawBody, $signature, $timestamp);
```

### Concurrency

```php
use GuzzleHttp\Promise\Utils;

$results = Utils::settle([
    $client->crm()->timeline()->createBatchAsync($batchA),
    $client->crm()->timeline()->createBatchAsync($batchB),
])->wait();

// or capped, for a large fan-out:
$results = $client->pool(
    array_map(fn ($b) => fn () => $client->crm()->timeline()->createBatchAsync($b), $batches),
    concurrency: 5,
);
```

## Errors and retries

Everything extends `HubSpot\Exceptions\HubSpotException`: `ApiException` (`->status`, `->body`, and HubSpot's own validation message), `RateLimitException` (`->retryAfter`), `AuthenticationException`. Transient failures retry with backoff, honoring `Retry-After`. A 429 retries on any method; a 5xx or dropped connection only on idempotent methods (never a POST/PATCH), so a create is never duplicated. Tune with `maxRetries` (`0` turns it off).

## Keeping paths current

Paths come from HubSpot's OpenAPI catalog, not guesswork. After a HubSpot release, run `composer refresh-specs` to regenerate `docs/verified-paths.md`, then bump `Client::DEFAULT_VERSION`.

## Development

```bash
composer test      # Pest, offline
composer analyse   # PHPStan level 6
composer lint      # Pint
```

Releases are automated via [release-please](https://github.com/googleapis/release-please).

## License

[MIT](LICENSE).
