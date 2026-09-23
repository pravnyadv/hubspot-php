# hubspot-php

[![Tests](https://github.com/pravnyadv/hubspot-php/actions/workflows/tests.yml/badge.svg)](https://github.com/pravnyadv/hubspot-php/actions/workflows/tests.yml)
[![Latest Version](https://img.shields.io/packagist/v/pravnyadv/hubspot-php.svg)](https://packagist.org/packages/pravnyadv/hubspot-php)
[![PHP Version](https://img.shields.io/packagist/php-v/pravnyadv/hubspot-php.svg)](https://packagist.org/packages/pravnyadv/hubspot-php)
[![License](https://img.shields.io/packagist/l/pravnyadv/hubspot-php.svg)](LICENSE)

A small, framework-free PHP client for HubSpot that speaks HubSpot's new date-based API versions.

```php
$client  = HubSpot::withAccessToken('pat-na1-...');
$contact = $client->crm()->contacts()->get('123', ['firstname', 'email']);

echo $contact->properties->email;
```

## Why this exists

HubSpot is moving its entire API off the old numbered versions (`v1` through `v4`) and onto date-based ones like `2026-09`. The numbered paths have a deadline: `v4` is scheduled to stop working in March 2027, and `v1` to `v3` in September 2027. Any integration still calling `/crm/v3/objects/...` is on a clock.

The official `hubspot/api-client` SDK does not support the date-based versions at all. I went looking before writing any of this: every generated path in the current release points at a numbered version, the GitHub issue asking for date-based support has gone unanswered, and a HubSpot blog post claiming the SDK already handles it is contradicted by the actual code in the repo. So if you reach for the official SDK today, you are pinned to the paths HubSpot is winding down, with no switch to flip.

This package solves that one problem and solves it well. Every resource maps to its correct dated path, and those paths are not guessed. They are pulled straight from HubSpot's own public OpenAPI catalog by `bin/pull-specs.php` and checked in at [`docs/verified-paths.md`](docs/verified-paths.md). When HubSpot ships the next dated release, you rerun that script and diff the file.

## How it differs from the official SDK

- **It uses date-based versions.** That is the whole reason it exists. The default is `2026-09`, and each family can be pinned independently (more below).
- **It returns plain data.** A `stdClass` by default, or an associative array if you ask for one. The official SDK hands back generated model objects that most callers immediately flatten anyway, and those models throw when HubSpot returns a field type they were not built for, which real Marketing Forms do. Here you get the decoded JSON and read it however suits you.
- **It is thin.** The only runtime dependency is Guzzle. No code generation, no framework glue, nothing to wire into a container. It drops into any project.
- **Each API family can sit on its own version.** HubSpot does not promote every family to a new date at the same time, so you can keep one resource on an older dated path while everything else moves ahead.
- **The useful plumbing is built in.** Automatic retries on 429 and 5xx (honoring `Retry-After`), cursor pagination, typed exceptions that surface HubSpot's own validation messages, and webhook signature verification.

The one thing it borrows from the official SDK on purpose is the shape of the API. Resources are grouped by product area, so `$client->crm()->contacts()` reads the way someone coming from HubSpot's own SDK would expect.

## Requirements

PHP 8.2 or newer, with `ext-json`. Guzzle 7.8+ comes in as a dependency.

## Installation

```bash
composer require pravnyadv/hubspot-php
```

## Getting started

Create a client with a private-app token and start calling resources. Responses come back as `stdClass`, so you read nested data with `->`.

```php
use HubSpot\HubSpot;

$client = HubSpot::withAccessToken('pat-na1-...');

$contact = $client->crm()->contacts()->get('123', ['firstname', 'email']);
echo $contact->properties->email;

$deals = $client->crm()->deals()->search([
    'filterGroups' => [[
        'filters' => [['propertyName' => 'dealstage', 'operator' => 'EQ', 'value' => 'closedwon']],
    ]],
    'properties' => ['dealname', 'amount'],
]);
```

Resources are grouped by product area, the same way HubSpot's own SDK groups them:

```php
$client->crm()->objects('contacts')   // generic, works for any object type including custom p_* ones
$client->crm()->contacts()            // typed aliases for the standard objects (deals, tickets, tasks, ...)
$client->crm()->lists() / owners() / properties() / associations() / pipelines() / schemas() / timeline()

$client->cms()->pages() / sourceCode() / blogPosts() / hubdb() / domains() / urlRedirects()
$client->marketing()->forms() / emails() / campaigns() / subscriptions()
$client->commerce()->paymentLinks() / priceBooks()
$client->automation()->flows() / sequences() / actions()
$client->conversations()->inbox() / customChannels() / visitorIdentification()
$client->settings()->teams() / users() / businessUnits() / webhooks()
$client->events() / account() / files() / scheduler()
```

If a corner of the API has no typed resource yet, you can still reach it through the transport:

```php
$data = $client->request('GET', '/crm/objects/2026-09/tickets/456');
```

### Pagination

Any paginated resource has an `all()` method that returns a `Paginator`. It walks HubSpot's `after` cursor for you, so you just iterate:

```php
foreach ($client->crm()->owners()->all() as $owner) {
    echo $owner->id, PHP_EOL;
}

$everyOwner = $client->crm()->owners()->all()->all(); // or collect the lot into one array
```

### Arrays instead of objects

If you would rather work with arrays, switch the whole client over, or override a single call:

```php
use HubSpot\ResponseFormat;

$client  = HubSpot::withAccessToken('pat-na1-...', responseFormat: ResponseFormat::Assoc);
$contact = $client->crm()->contacts()->get('123');
echo $contact['properties']['email'];
```

### Pinning a family to a different version

When HubSpot has shipped a new dated version for some families but not others, pass a version to just the resource that needs it. Everything else keeps the client default.

```php
$contact = $client->crm()->contacts()->get('123');       // .../2026-09/objects/contacts/123
$list    = $client->crm()->lists('2026-03')->get('789');  // .../2026-03/... for lists only
```

Or set a different default across the board at construction:

```php
$client = HubSpot::withAccessToken('pat-na1-...', version: '2026-03');
```

### Building a search request

`SearchRequest` assembles a search body without the nested-array bookkeeping. Filters inside a group are AND-ed, and groups are OR-ed together.

```php
use HubSpot\Search\SearchRequest;

$request = SearchRequest::create()
    ->where('email', 'HAS_PROPERTY')
    ->where('lastname', 'EQ', 'Doe')
    ->properties(['firstname', 'lastname', 'email'])
    ->sort('createdate', 'DESCENDING')
    ->limit(50);

$results = $client->crm()->contacts()->search($request->toArray());
```

### OAuth

This package handles the token dance and refreshes expired tokens on the fly. It does not store tokens for you. You register a callback and persist each refreshed `TokenSet` yourself.

```php
use HubSpot\HubSpot;
use HubSpot\Auth\OAuthAuth;
use HubSpot\Auth\TokenSet;

$oauth = HubSpot::oauth();

// Send the user here to authorize
$url = $oauth->authorizationUrl(
    'your-client-id',
    'https://your-app.example.com/oauth/callback',
    ['crm.objects.contacts.read', 'crm.lists.read'],
);

// In your callback handler, trade the code for tokens
$tokens = $oauth->exchangeAuthorizationCode(
    'your-client-id',
    'your-client-secret',
    $_GET['code'],
    'https://your-app.example.com/oauth/callback',
);

// Build a client that refreshes itself and tells you when it does
$auth = (new OAuthAuth($oauth, 'your-client-id', 'your-client-secret', $tokens))
    ->onTokenRefreshed(function (TokenSet $t) use ($db): void {
        $db->saveTokens($t->accessToken, $t->refreshToken, $t->expiresAt);
    });

$client = HubSpot::withOAuth($auth);
```

### Verifying webhooks

```php
$webhooks = HubSpot::webhooks('your-client-secret');

$valid = $webhooks->isValidV3(
    $_SERVER['REQUEST_METHOD'],
    'https://your-app.example.com/webhook',        // the full URL HubSpot signed
    file_get_contents('php://input'),              // the raw request body
    $_SERVER['HTTP_X_HUBSPOT_SIGNATURE_V3'],
    $_SERVER['HTTP_X_HUBSPOT_REQUEST_TIMESTAMP'],  // epoch milliseconds, as HubSpot sends it
);

if (! $valid) {
    http_response_code(401);
    exit;
}
```

A v2 fallback, `isValidV2($method, $uri, $body, $signature)`, is there if you need it.

## Errors

Everything the client throws extends `HubSpot\Exceptions\HubSpotException`. A non-2xx response is an `ApiException` carrying `->status` and the decoded `->body`, and its message is HubSpot's own validation detail when there is one, so a rejected write reads as the real reason rather than a bare status code. A 429 is a `RateLimitException` with `->retryAfter`, and an OAuth token failure is an `AuthenticationException`.

Transient failures are retried with backoff (honoring `Retry-After`) before any of that surfaces. The retry is idempotency-aware: a 429 is retried on any method, but a 5xx or dropped connection is retried only on idempotent methods (GET, HEAD, PUT, DELETE), never a POST or PATCH, so a create that may have already gone through is not duplicated. Tune it at construction:

```php
$client = HubSpot::withAccessToken('pat-na1-...', maxRetries: 5); // default is 3
$client = HubSpot::withAccessToken('pat-na1-...', maxRetries: 0); // turn retries off
```

## Concurrency

For firing many requests at once, `sendAsync()`/`requestAsync()` return Guzzle promises, and `pool()` runs them with a bounded concurrency cap:

```php
use GuzzleHttp\Promise\Utils;

// A few at once, inspect each outcome
$results = Utils::settle([
    $client->crm()->timeline()->createBatchAsync($batchA),
    $client->crm()->timeline()->createBatchAsync($batchB),
])->wait();

// Many, capped so you do not open hundreds of connections
$results = $client->pool(
    array_map(fn ($b) => fn () => $client->crm()->timeline()->createBatchAsync($b), $batches),
    concurrency: 5,
);
```

## Staying current with HubSpot releases

The default version is `2026-09`. Marketing Forms and Automation Flows run on `2026-09-beta`, and OAuth on `2026-03`, because those are the only dated paths HubSpot currently publishes for them.

When HubSpot ships a new dated release:

1. Run `composer refresh-specs`. It pulls HubSpot's public OpenAPI catalog and rewrites `docs/verified-paths.md`.
2. Diff that file to see which families moved.
3. Bump `Client::DEFAULT_VERSION` (or a single resource's version) and adjust any changed paths.
4. Commit with a `feat:` message. Releases and tags are automated (see below).

## Contributing and releases

Run the checks locally before opening a pull request:

```bash
composer test      # Pest, fully offline
composer analyse   # PHPStan level 6
composer lint      # Laravel Pint (check only)
```

There is also a read-only live smoke test at `bin/smoke-test.php` for sanity-checking against a real portal:

```bash
HUBSPOT_TOKEN=pat-na1-... php bin/smoke-test.php
```

Releases run through [release-please](https://github.com/googleapis/release-please). Commits follow [Conventional Commits](https://www.conventionalcommits.org/), so merging to `main` opens a release pull request with the version bump and changelog already filled in. Merging that PR tags the release and publishes it to Packagist.

## License

MIT. See [LICENSE](LICENSE).
