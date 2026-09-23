<?php

declare(strict_types=1);

use HubSpot\Pagination\Paginator;

it('sitePages() hits the correct path with archived=false as a string', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['results' => [['id' => 'sp1']]]),
    ]);

    $result = $client->cms()->pages()->sitePages();

    $uri = $mock->getLastRequest()->getUri();
    expect($uri->getPath())->toBe('/cms/pages/2026-09/site-pages');
    parse_str($uri->getQuery(), $query);
    expect($query['archived'])->toBe('false');
    expect($result['results'])->toBe([['id' => 'sp1']]);
});

it('sitePages() sends archived=true when requested', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['results' => []]),
    ]);

    $client->cms()->pages()->sitePages(archived: true);

    parse_str($mock->getLastRequest()->getUri()->getQuery(), $query);
    expect($query['archived'])->toBe('true');
});

it('sitePages() passes after cursor when supplied', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['results' => []]),
    ]);

    $client->cms()->pages()->sitePages(after: 'page-2');

    parse_str($mock->getLastRequest()->getUri()->getQuery(), $query);
    expect($query['after'])->toBe('page-2');
});

it('landingPages() hits the correct path with archived=false as a string', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['results' => [['id' => 'lp1']]]),
    ]);

    $result = $client->cms()->pages()->landingPages();

    $uri = $mock->getLastRequest()->getUri();
    expect($uri->getPath())->toBe('/cms/pages/2026-09/landing-pages');
    parse_str($uri->getQuery(), $query);
    expect($query['archived'])->toBe('false');
    expect($result['results'])->toBe([['id' => 'lp1']]);
});

it('a per-resource version override replaces the date segment for sitePages', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['results' => []]),
    ]);

    $client->cms()->pages('2026-03')->sitePages();

    expect($mock->getLastRequest()->getUri()->getPath())->toBe('/cms/pages/2026-03/site-pages');
});

it('allSitePages() returns a Paginator that walks paging.next.after', function () {
    [$client] = mockClient([
        jsonResponse(200, [
            'results' => [['id' => 'sp1']],
            'paging' => ['next' => ['after' => 'cursor-1']],
        ]),
        jsonResponse(200, [
            'results' => [['id' => 'sp2']],
        ]),
    ]);

    $paginator = $client->cms()->pages()->allSitePages();
    expect($paginator)->toBeInstanceOf(Paginator::class);
    expect($paginator->all())->toBe([['id' => 'sp1'], ['id' => 'sp2']]);
});

it('allLandingPages() returns a Paginator that walks paging.next.after', function () {
    [$client] = mockClient([
        jsonResponse(200, [
            'results' => [['id' => 'lp1']],
            'paging' => ['next' => ['after' => 'cursor-1']],
        ]),
        jsonResponse(200, [
            'results' => [['id' => 'lp2']],
        ]),
    ]);

    $paginator = $client->cms()->pages()->allLandingPages();
    expect($paginator)->toBeInstanceOf(Paginator::class);
    expect($paginator->all())->toBe([['id' => 'lp1'], ['id' => 'lp2']]);
});
