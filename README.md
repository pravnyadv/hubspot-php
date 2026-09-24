# hubspot-php

[![Tests](https://github.com/pravnyadv/hubspot-php/actions/workflows/tests.yml/badge.svg)](https://github.com/pravnyadv/hubspot-php/actions/workflows/tests.yml)
[![Latest Version](https://img.shields.io/packagist/v/pravnyadv/hubspot-php.svg)](https://packagist.org/packages/pravnyadv/hubspot-php)
[![PHP Version](https://img.shields.io/packagist/php-v/pravnyadv/hubspot-php.svg)](https://packagist.org/packages/pravnyadv/hubspot-php)
[![License](https://img.shields.io/github/license/pravnyadv/hubspot-php.svg)](LICENSE)

A small, framework-free PHP client for HubSpot's date-based API versions (`2026-09` and later). The official SDK still calls the numbered versions HubSpot is retiring in 2027; this maps every resource to its correct dated path, read from HubSpot's own OpenAPI catalog.

Plain `stdClass`/array responses (no model classes), a grouped facade, per-family version pinning, auto-refreshing OAuth, idempotency-aware retries, cursor pagination, typed exceptions parsed from HubSpot's error schema, PSR-3 logging with your own context, webhook verification, and async with a concurrency pool.

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

Resources are grouped by product area (`/` means "or" between method names, not
division — this isn't valid PHP, just a compact reference):

```text
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

### Records that may not exist

`get()` throws `NotFoundException` on a 404. `find()` returns null instead, and still throws on every other failure, so a rate limit or outage never looks like a missing record.

```php
$contact = $client->crm()->contacts()->find('jane@example.com', ['email'], idProperty: 'email');
```

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

## Errors

Every failed request throws an `ApiException` subclass picked by status, so you catch what you actually handle:

| Status | Exception | Extra |
|---|---|---|
| 400, 422 | `ValidationException` | |
| 401 | `AuthenticationException` | also OAuth refresh failures |
| 403 | `ForbiddenException` | `->missingScopes()` |
| 404 | `NotFoundException` | |
| 409 | `ConflictException` | `->existingId()` |
| 429 | `RateLimitException` | `->retryAfter` |
| 5xx | `ServerException` | |
| none | `ConnectionException` | DNS, timeout; status 0 |

Each one has `->status`, `->body`, and `->error`: HubSpot's error body parsed into a `HubSpotError` (`category`, `subCategory`, `correlationId`, `context`, `errors`), the same shape across every API. `getMessage()` is HubSpot's own message, with the first field error pulled out of validation failures.

Transient failures retry with backoff, honoring `Retry-After`. A 429 retries on any method; a 5xx or dropped connection only on idempotent methods (never a POST/PATCH), so a create is never duplicated. Tune with `maxRetries` (`0` turns it off).

### Handling failures

The client throws on failure; it never returns null or false instead. Catch only what you can act on, and let the rest reach your job or error handler, where a retry is usually right.

```php
// A record that may not exist needs no try/catch.
$contact = $client->crm()->contacts()->find($id);

// A failure you can act on: catch just that one.
try {
    $client->crm()->contacts()->create(['email' => $email]);
} catch (ConflictException $e) {
    $client->crm()->contacts()->update($e->existingId() ?? throw $e, $properties);
}
```

Avoid `catch (ApiException) { return null; }`. It turns a rate limit or an outage into "not found", and whatever acts on that null then does the wrong thing quietly. In a queue job, catch `RateLimitException` and re-queue it after `->retryAfter` seconds.

## Logging and context

Pass any PSR-3 logger and whatever ids you trace by. Every attempt is logged once (debug on success, info on a 4xx, warning on a 429, 5xx or dropped connection) with method, path, status, duration and HubSpot's correlation id. Bodies, query strings and the token are never logged, though a path can hold an id you looked up by, such as an email with `idProperty: 'email'`.

```php
$client = HubSpot::withAccessToken($token, logger: $logger, context: ['portal_id' => $portalId]);

$job = $client->withContext(['request_id' => $requestId]);   // a copy for one job
```

The same context is on every exception through `$e->context()`, which Laravel merges into the log entry when it reports the exception.

## Middleware

Guzzle middleware added here runs once per attempt, inside the retries, so a rate limiter counts every request HubSpot receives. Exceptions it throws reach you untouched.

```php
$client = HubSpot::withAccessToken($token, middleware: ['rate_limit' => $limiter]);
```

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
