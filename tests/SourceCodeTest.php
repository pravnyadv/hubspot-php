<?php

declare(strict_types=1);

use GuzzleHttp\Psr7\Response;

it('get() returns the raw file body and keeps path slashes literal, not percent-encoded', function () {
    [$client, $mock] = mockClient([new Response(200, ['Content-Type' => 'text/html'], '<!-- home template -->')]);

    $result = $client->cms()->sourceCode()->get('published', 'my-theme/templates/home.html');

    expect($result)->toBe('<!-- home template -->');

    $path = $mock->getLastRequest()->getUri()->getPath();
    expect($path)->toBe('/cms/source-code/2026-09/published/content/my-theme/templates/home.html');
    expect($path)->toContain('/templates/home.html');
    expect($path)->not->toContain('%2F');
});

it('get() percent-encodes spaces within a segment while keeping slashes literal', function () {
    [$client, $mock] = mockClient([new Response(200, ['Content-Type' => 'text/html'], 'body')]);

    $client->cms()->sourceCode()->get('published', 'my-theme/templates/home page.html');

    $path = $mock->getLastRequest()->getUri()->getPath();
    expect($path)->toContain('%20');
    expect($path)->not->toContain('%2F');
});

it('put() sends PUT with multipart/form-data and the file content in the body', function () {
    [$client, $mock] = mockClient([jsonResponse(200, ['id' => 'file-abc'])]);

    $result = $client->cms()->sourceCode()->put('published', 'my-theme/templates/home.html', '<html>content</html>');

    expect($result)->toBe(['id' => 'file-abc']);

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('PUT');
    expect($request->getHeaderLine('Content-Type'))->toContain('multipart/form-data');
    expect($request->getUri()->getPath())->toContain('/published/');
    expect((string) $request->getBody())->toContain('<html>content</html>');
});

it('put() uses the supplied filename override in the multipart part', function () {
    [$client, $mock] = mockClient([jsonResponse(200, [])]);

    $client->cms()->sourceCode()->put('draft', 'my-theme/templates/home.html', 'body', 'custom-name.html');

    expect((string) $mock->getLastRequest()->getBody())->toContain('custom-name.html');
});

it('archive() sends DELETE for the correct versioned path', function () {
    [$client, $mock] = mockClient([new Response(204)]);

    $client->cms()->sourceCode()->archive('published', 'my-theme/templates/home.html');

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('DELETE');
    expect($request->getUri()->getPath())
        ->toBe('/cms/source-code/2026-09/published/content/my-theme/templates/home.html');
});

it('respects a per-resource version override in the path date segment', function () {
    [$client, $mock] = mockClient([new Response(200, ['Content-Type' => 'text/html'], 'body')]);

    $client->cms()->sourceCode('2026-03')->get('published', 'my-theme/templates/home.html');

    expect($mock->getLastRequest()->getUri()->getPath())
        ->toBe('/cms/source-code/2026-03/published/content/my-theme/templates/home.html');
});
