<?php

declare(strict_types=1);

/**
 * Dev harness: resolve HubSpot's date-based OpenAPI specs from the public spec
 * catalog and distil them into docs/verified-paths.{json,md}, the ground-truth
 * path map this package is built against.
 *
 * Run: php bin/pull-specs.php
 *
 * Why this exists: the machine catalog can lag behind releases.
 * The public GitHub spec collection is the source for versions and paths.
 * Read the distilled docs/verified-paths.md, never the multi-thousand-line raw
 * specs, when you need a path.
 */
const COLLECTION = 'https://api.github.com/repos/HubSpot/HubSpot-public-api-spec-collection/git/trees/main?recursive=1';
const RAW = 'https://raw.githubusercontent.com/HubSpot/HubSpot-public-api-spec-collection/main/';
const CATALOG = 'https://api.hubapi.com/public/api/spec/v2/specs';
const CACHE_DIR = __DIR__.'/../.spec-cache';
const OUT_JSON = __DIR__.'/../docs/verified-paths.json';
const OUT_MD = __DIR__.'/../docs/verified-paths.md';

/**
 * The APIs this package covers, keyed by our short name. `version` is a regex
 * anchoring which release we pull. The version segment is product-specific
 * (CRM is 2026-09, Forms is 2027-03-beta), it is NOT a global
 * date prefix, so each entry pins its own.
 */
const WANTED = [
    'crm-objects' => ['group' => 'CRM',       'name' => 'Custom Objects', 'version' => '/^2026-09$/'],
    'crm-lists' => ['group' => 'CRM',       'name' => 'Lists',          'version' => '/^2026-09$/'],
    'crm-owners' => ['group' => 'CRM',       'name' => 'Crm Owners',     'version' => '/^2026-09$/'],
    'crm-props' => ['group' => 'CRM',       'name' => 'Properties',     'version' => '/^2026-09$/'],
    'account-info' => ['group' => 'Account',   'name' => 'Account Info',   'version' => '/^2026-09$/'],
    'forms' => ['group' => 'Marketing', 'name' => 'Forms',          'version' => '/^2027-03-beta$/'],
    'cms-pages' => ['group' => 'CMS',       'name' => 'Pages',          'version' => '/^2026-09$/'],
    'cms-source' => ['group' => 'CMS',       'name' => 'Source Code',    'version' => '/^2026-09$/'],
    'associations' => ['group' => 'CRM',       'name' => 'Associations',   'version' => '/^2026-09$/'],
    'timeline' => ['group' => 'CRM',       'name' => 'Timeline',       'version' => '/^2026-09$/'],
    'pipelines' => ['group' => 'CRM',       'name' => 'Pipelines',      'version' => '/^2026-09$/'],
    'schemas' => ['group' => 'CRM',       'name' => 'Schemas',        'version' => '/^2026-09$/'],
    'files' => ['group' => 'Files',     'name' => 'Files',          'version' => '/^2026-09$/'],
    'comm-prefs' => ['group' => 'Communication Preferences', 'name' => 'Subscriptions', 'version' => '/^2026-09$/'],
    'conversations' => ['group' => 'Conversations', 'name' => 'Conversations', 'version' => '/^2026-09$/'],
    'automation' => ['group' => 'Automation', 'name' => 'Automation V4', 'version' => '/^2027-03-beta$/'],
    // Expansion: distinct non-object public families.
    'blog-posts' => ['group' => 'CMS', 'name' => 'Posts', 'version' => '/^2026-09$/'],
    'blog-authors' => ['group' => 'CMS', 'name' => 'Authors', 'version' => '/^2026-09$/'],
    'blog-settings' => ['group' => 'CMS', 'name' => 'Blog Settings', 'version' => '/^2026-09$/'],
    'blog-tags' => ['group' => 'CMS', 'name' => 'Tags', 'version' => '/^2026-09$/'],
    'hubdb' => ['group' => 'CMS', 'name' => 'Hubdb', 'version' => '/^2026-09$/'],
    'cms-domains' => ['group' => 'CMS', 'name' => 'Domains', 'version' => '/^2026-09$/'],
    'url-redirects' => ['group' => 'CMS', 'name' => 'Url Redirects', 'version' => '/^2026-09$/'],
    'marketing-emails' => ['group' => 'Marketing', 'name' => 'Marketing Emails', 'version' => '/^2026-09$/'],
    'marketing-events' => ['group' => 'Marketing', 'name' => 'Marketing Events', 'version' => '/^2026-09$/'],
    'campaigns' => ['group' => 'Marketing', 'name' => 'Campaigns Public Api', 'version' => '/^2026-09$/'],
    'single-send' => ['group' => 'Marketing', 'name' => 'Single-send', 'version' => '/^2026-09$/'],
    'transactional' => ['group' => 'Marketing', 'name' => 'Transactional Single Send', 'version' => '/^2026-09$/'],
    'automation-actions' => ['group' => 'Automation', 'name' => 'Actions V4', 'version' => '/^2026-09$/'],
    'sequences' => ['group' => 'Automation', 'name' => 'Sequences', 'version' => '/^2026-09$/'],
    'events' => ['group' => 'Events', 'name' => 'Events', 'version' => '/^2026-09$/'],
    'event-definitions' => ['group' => 'Events', 'name' => 'Manage Event Definitions', 'version' => '/^2026-09$/'],
    'send-events' => ['group' => 'Events', 'name' => 'Send Event Completions', 'version' => '/^2026-09$/'],
    'custom-channels' => ['group' => 'Conversations', 'name' => 'Custom Channels', 'version' => '/^2026-09$/'],
    'visitor-id' => ['group' => 'Conversations', 'name' => 'Visitor Identification', 'version' => '/^2026-09$/'],
    'payment-links' => ['group' => 'Commerce', 'name' => 'Payment Links', 'version' => '/^2026-09$/'],
    'price-books' => ['group' => 'Commerce', 'name' => 'Price Books', 'version' => '/^2026-09$/'],
    'crm-imports' => ['group' => 'CRM', 'name' => 'Imports', 'version' => '/^2026-09$/'],
    'crm-exports' => ['group' => 'CRM', 'name' => 'Exports', 'version' => '/^2026-09$/'],
    'crm-users' => ['group' => 'CRM', 'name' => 'Users', 'version' => '/^2026-09$/'],
    'object-tags' => ['group' => 'CRM', 'name' => 'Object Tags', 'version' => '/^2026-09$/'],
    'settings-teams' => ['group' => 'Settings', 'name' => 'Teams', 'version' => '/^2026-09$/'],
    'user-provisioning' => ['group' => 'Settings', 'name' => 'User Provisioning', 'version' => '/^2026-09$/'],
    'webhooks' => ['group' => 'Webhooks', 'name' => 'Webhooks', 'version' => '/^2026-09$/'],
    'business-units' => ['group' => 'Business Units', 'name' => 'Business Units', 'version' => '/^2026-09$/'],
    'scheduler-meetings' => ['group' => 'Scheduler', 'name' => 'Meetings', 'version' => '/^2026-09$/'],
    'oauth' => ['group' => 'Auth', 'name' => 'Oauth', 'version' => '/^2026-09$/'],
];

function http_get(string $url): string
{
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 40,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTPHEADER => ['Accept: application/json', 'User-Agent: hubspot-php-sdk'],
    ]);
    $body = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err = curl_error($ch);
    curl_close($ch);

    if ($body === false || $status >= 400) {
        fwrite(STDERR, "GET {$url} failed (HTTP {$status}) {$err}\n");
        exit(1);
    }

    return $body;
}

/** @return array<string, mixed> */
function cached_json(string $key, string $url): array
{
    @mkdir(CACHE_DIR, 0777, true);
    $file = CACHE_DIR.'/'.$key.'.json';
    $body = is_file($file) ? (string) file_get_contents($file) : http_get($url);
    file_put_contents($file, $body);

    return json_decode($body, true, flags: JSON_THROW_ON_ERROR);
}

/**
 * Date-based releases newer than $pinned on the same channel: a stable pin
 * ignores betas and a "-beta" pin ignores stable.
 *
 * @param  list<array{releaseVersion: string, stage: string}>  $releases
 * @return list<array{releaseVersion: string, stage: string}>
 */
function newer_releases(array $releases, string $pinned): array
{
    $isBeta = str_ends_with($pinned, '-beta');

    return array_values(array_filter($releases, fn (array $r): bool => preg_match('/^\d{4}-\d{2}/', $r['releaseVersion']) === 1
        && str_ends_with($r['releaseVersion'], '-beta') === $isBeta
        && strcmp($r['releaseVersion'], $pinned) > 0));
}

echo "Fetching collection and catalog...\n";
// Always fresh: cached release lists would hide newer releases.
$collection = json_decode(http_get(COLLECTION), true, flags: JSON_THROW_ON_ERROR);
if ($collection['truncated'] ?? true) {
    fwrite(STDERR, "Collection tree is truncated or invalid.\n");
    exit(1);
}
$catalog = json_decode(http_get(CATALOG), true, flags: JSON_THROW_ON_ERROR);

$outdated = 0;

$result = [];
foreach (WANTED as $key => $want) {
    $stages = [];
    foreach ($catalog['results'] as $api) {
        if ($api['group'] === $want['group'] && $api['name'] === $want['name']) {
            foreach ($api['releases'] as $r) {
                $stages[$r['releaseVersion']] = $r['stage'];
            }
            break;
        }
    }

    $releases = [];
    $prefix = 'PublicApiSpecs/'.$want['group'].'/'.$want['name'].'/Rollouts/';
    foreach ($collection['tree'] as $item) {
        $path = $item['path'];
        if (! str_starts_with($path, $prefix) || ! preg_match('~^(\d+)/(\d{4}-\d{2}[^/]*)/[^/]+\.json$~', substr($path, strlen($prefix)), $match)) {
            continue;
        }
        [, $rollout, $version] = $match;
        if (! isset($releases[$version]) || (int) $rollout > (int) $releases[$version]['rollout']) {
            $releases[$version] = [
                'releaseVersion' => $version,
                'stage' => $stages[$version] ?? 'UNLISTED',
                'rollout' => $rollout,
                'openApi' => RAW.implode('/', array_map('rawurlencode', explode('/', $path))),
            ];
        }
    }
    krsort($releases);
    $release = null;
    foreach ($releases as $r) {
        if (preg_match($want['version'], $r['releaseVersion']) === 1) {
            $release = $r;
            break;
        }
    }
    if ($release === null) {
        fwrite(STDERR, "  ! no release matching {$want['version']} for {$want['name']}\n");

        continue;
    }

    echo "  {$key}: {$want['name']} @ {$release['releaseVersion']} [{$release['stage']}]\n";
    foreach (newer_releases(array_values($releases), $release['releaseVersion']) as $r) {
        fwrite(STDERR, "  ! {$key}: newer release {$r['releaseVersion']} [{$r['stage']}]\n");
        $outdated++;
    }
    $spec = cached_json("collection-{$key}@{$release['releaseVersion']}-{$release['rollout']}", $release['openApi']);
    $paths = array_keys($spec['paths'] ?? []);
    sort($paths);

    $result[$key] = [
        'group' => $want['group'],
        'name' => $want['name'],
        'version' => $release['releaseVersion'],
        'stage' => $release['stage'],
        'openApi' => $release['openApi'],
        'paths' => $paths,
    ];
}

file_put_contents(OUT_JSON, json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)."\n");

$md = "# Verified HubSpot date-based paths\n\n"
    ."Generated by `bin/pull-specs.php` from HubSpot's public OpenAPI spec collection. "
    ."These paths are read straight from HubSpot's own specs, not doc prose. "
    ."Regenerate after a HubSpot release. Do not hand-edit.\n\n"
    ."| Key | Version | Stage | Path count |\n|---|---|---|---|\n";
foreach ($result as $key => $r) {
    $md .= "| `{$key}` | `{$r['version']}` | {$r['stage']} | ".count($r['paths'])." |\n";
}
$md .= "\n";
foreach ($result as $key => $r) {
    $md .= "## {$key} ({$r['group']} / {$r['name']}): `{$r['version']}`\n\n";
    foreach ($r['paths'] as $p) {
        $md .= "- `{$p}`\n";
    }
    $md .= "\n";
}
file_put_contents(OUT_MD, $md);

echo 'Wrote '.OUT_JSON.' and '.OUT_MD."\n";

if ($outdated > 0) {
    fwrite(STDERR, "{$outdated} newer release(s) available; bump the pins above.\n");
    exit(2);
}
