<?php

declare(strict_types=1);

/**
 * Dev harness: resolve HubSpot's date-based OpenAPI specs from the public spec
 * catalog and distil them into docs/verified-paths.{json,md} — the ground-truth
 * path map this package is built against.
 *
 * Run: php bin/pull-specs.php
 *
 * Why this exists: HubSpot's doc *pages* hallucinate (fake URLs, wrong dates),
 * but the spec catalog is machine-readable and authoritative, and needs no auth.
 * Read the distilled docs/verified-paths.md, never the multi-thousand-line raw
 * specs, when you need a path.
 */
const CATALOG = 'https://api.hubapi.com/public/api/spec/v2/specs';
const CACHE_DIR = __DIR__.'/../.spec-cache';
const OUT_JSON = __DIR__.'/../docs/verified-paths.json';
const OUT_MD = __DIR__.'/../docs/verified-paths.md';

/**
 * The APIs this package covers, keyed by our short name. `version` is a regex
 * anchoring which release we pull — the version segment is product-specific
 * (CRM is 2026-09, Forms is 2026-09-beta, OAuth is 2026-03), it is NOT a global
 * date prefix, so each entry pins its own.
 */
const WANTED = [
    'crm-objects' => ['group' => 'CRM',       'name' => 'Custom Objects', 'version' => '/^2026-09$/'],
    'crm-lists' => ['group' => 'CRM',       'name' => 'Lists',          'version' => '/^2026-09$/'],
    'crm-owners' => ['group' => 'CRM',       'name' => 'Crm Owners',     'version' => '/^2026-09$/'],
    'crm-props' => ['group' => 'CRM',       'name' => 'Properties',     'version' => '/^2026-09$/'],
    'account-info' => ['group' => 'Account',   'name' => 'Account Info',   'version' => '/^2026-09$/'],
    'forms' => ['group' => 'Marketing', 'name' => 'Forms',          'version' => '/^2026-09-beta$/'],
    'cms-pages' => ['group' => 'CMS',       'name' => 'Pages',          'version' => '/^2026-09$/'],
    'cms-source' => ['group' => 'CMS',       'name' => 'Source Code',    'version' => '/^2026-09$/'],
    'associations' => ['group' => 'CRM',       'name' => 'Associations',   'version' => '/^2026-09$/'],
    'timeline' => ['group' => 'CRM',       'name' => 'Timeline',       'version' => '/^2026-09$/'],
    'pipelines' => ['group' => 'CRM',       'name' => 'Pipelines',      'version' => '/^2026-09$/'],
    'schemas' => ['group' => 'CRM',       'name' => 'Schemas',        'version' => '/^2026-09$/'],
    'files' => ['group' => 'Files',     'name' => 'Files',          'version' => '/^2026-09$/'],
    'comm-prefs' => ['group' => 'Communication Preferences', 'name' => 'Subscriptions', 'version' => '/^2026-09$/'],
    'conversations' => ['group' => 'Conversations', 'name' => 'Conversations', 'version' => '/^2026-09$/'],
    'automation' => ['group' => 'Automation', 'name' => 'Automation V4', 'version' => '/^2026-09-beta$/'],
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
];

function http_get(string $url): string
{
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 40,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTPHEADER => ['Accept: application/json'],
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

echo "Fetching catalog...\n";
$catalog = cached_json('_catalog', CATALOG);

$result = [];
foreach (WANTED as $key => $want) {
    $entry = null;
    foreach ($catalog['results'] as $api) {
        if ($api['group'] === $want['group'] && $api['name'] === $want['name']) {
            $entry = $api;
            break;
        }
    }
    if ($entry === null) {
        fwrite(STDERR, "  ! not found in catalog: {$want['group']}/{$want['name']}\n");

        continue;
    }

    $release = null;
    foreach ($entry['releases'] as $r) {
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
    $spec = cached_json($key, $release['openApi']);
    $paths = array_keys($spec['paths'] ?? []);
    sort($paths);

    $result[$key] = [
        'group' => $entry['group'],
        'name' => $entry['name'],
        'version' => $release['releaseVersion'],
        'stage' => $release['stage'],
        'openApi' => $release['openApi'],
        'paths' => $paths,
    ];
}

file_put_contents(OUT_JSON, json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)."\n");

$md = "# Verified HubSpot date-based paths\n\n"
    ."Generated by `bin/pull-specs.php` from HubSpot's public OpenAPI spec catalog. "
    ."These paths are read straight from HubSpot's own specs, not doc prose. "
    ."Regenerate after a HubSpot release. Do not hand-edit.\n\n"
    ."| Key | Version | Stage | Path count |\n|---|---|---|---|\n";
foreach ($result as $key => $r) {
    $md .= "| `{$key}` | `{$r['version']}` | {$r['stage']} | ".count($r['paths'])." |\n";
}
$md .= "\n";
foreach ($result as $key => $r) {
    $md .= "## {$key} ({$r['group']} / {$r['name']}) — `{$r['version']}`\n\n";
    foreach ($r['paths'] as $p) {
        $md .= "- `{$p}`\n";
    }
    $md .= "\n";
}
file_put_contents(OUT_MD, $md);

echo 'Wrote '.OUT_JSON.' and '.OUT_MD."\n";
