<?php

declare(strict_types=1);

use GuzzleHttp\Psr7\Response;

it('upload() sends POST /files/2026-09/files with multipart/form-data and file + options in the body', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['id' => 'file-123', 'name' => 'photo.jpg']),
    ]);

    $result = $client->files()->upload('binary-contents', 'photo.jpg', ['access' => 'PUBLIC_INDEXABLE']);

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('POST');
    expect($request->getUri()->getPath())->toBe('/files/2026-09/files');
    expect($request->getHeaderLine('Content-Type'))->toContain('multipart/form-data');

    $body = (string) $request->getBody();
    expect($body)->toContain('binary-contents');
    expect($body)->toContain('photo.jpg');
    expect($body)->toContain('"access"');

    expect($result)->toBe(['id' => 'file-123', 'name' => 'photo.jpg']);
});

it('upload() includes the folderPath part when provided', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['id' => 'file-456']),
    ]);

    $client->files()->upload('data', 'doc.pdf', [], '/my-folder');

    $body = (string) $mock->getLastRequest()->getBody();
    expect($body)->toContain('folderPath');
    expect($body)->toContain('/my-folder');
});

it('upload() omits folderPath part when not provided', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, []),
    ]);

    $client->files()->upload('data', 'doc.pdf');

    expect((string) $mock->getLastRequest()->getBody())->not->toContain('folderPath');
});

it('upload() encodes empty options as a JSON object not an array', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, []),
    ]);

    $client->files()->upload('data', 'doc.pdf');

    expect((string) $mock->getLastRequest()->getBody())->toContain('{}');
});

it('get() sends GET /files/2026-09/files/{fileId}', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['id' => 'file-123', 'name' => 'photo.jpg']),
    ]);

    $result = $client->files()->get('file-123');

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('GET');
    expect($request->getUri()->getPath())->toBe('/files/2026-09/files/file-123');
    expect($result)->toBe(['id' => 'file-123', 'name' => 'photo.jpg']);
});

it('list() sends GET /files/2026-09/files/search with query params', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['results' => [['id' => 'file-1']]]),
    ]);

    $result = $client->files()->list(['limit' => 10, 'offset' => 'tok']);

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('GET');
    expect($request->getUri()->getPath())->toBe('/files/2026-09/files/search');
    parse_str($request->getUri()->getQuery(), $query);
    expect($query['limit'])->toBe('10');
    expect($query['offset'])->toBe('tok');
    expect($result)->toBe(['results' => [['id' => 'file-1']]]);
});

it('list() omits null query values', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['results' => []]),
    ]);

    $client->files()->list(['limit' => 10, 'after' => null]);
    expect($mock->getLastRequest()->getUri()->getPath())->toBe('/files/2026-09/files/search');

    parse_str($mock->getLastRequest()->getUri()->getQuery(), $query);
    expect($query)->not->toHaveKey('after');
    expect($query['limit'])->toBe('10');
});

it('signedUrl() sends GET /files/2026-09/files/{fileId}/signed-url', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['url' => 'https://example.com/signed']),
    ]);

    $result = $client->files()->signedUrl('file-123');

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('GET');
    expect($request->getUri()->getPath())->toBe('/files/2026-09/files/file-123/signed-url');
    expect($result)->toBe(['url' => 'https://example.com/signed']);
});

it('download() sends GET /files/2026-09/files/{fileId}/download and returns raw bytes', function () {
    [$client, $mock] = mockClient([
        new Response(200, ['Content-Type' => 'image/jpeg'], 'raw-binary-data'),
    ]);

    $result = $client->files()->download('file-123');

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('GET');
    expect($request->getUri()->getPath())->toBe('/files/2026-09/files/file-123/download');
    expect($result)->toBe('raw-binary-data');
});

it('delete() sends DELETE /files/2026-09/files/{fileId}', function () {
    [$client, $mock] = mockClient([
        new Response(204),
    ]);

    $client->files()->delete('file-123');

    $request = $mock->getLastRequest();
    expect($request->getMethod())->toBe('DELETE');
    expect($request->getUri()->getPath())->toBe('/files/2026-09/files/file-123');
});

it('version override changes the date segment in the path', function () {
    [$client, $mock] = mockClient([
        jsonResponse(200, ['id' => 'file-123']),
    ]);

    $client->files('2026-03')->get('file-123');

    expect($mock->getLastRequest()->getUri()->getPath())->toBe('/files/2026-03/files/file-123');
});
