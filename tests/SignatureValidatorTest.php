<?php

declare(strict_types=1);

use HubSpot\Webhooks\SignatureValidator;

// v3 tests

it('validates a correct v3 signature', function (): void {
    $secret = 'test-secret';
    $method = 'POST';
    $uri = 'https://example.com/webhook';
    $body = '{"event":"contact.created"}';
    $timestamp = '1700000000000';
    // 100 seconds after timestamp, well within 300s window
    $nowMs = 1700000100000;

    $source = $method.$uri.$body.$timestamp;
    $signature = base64_encode(hash_hmac('sha256', $source, $secret, true));

    $validator = new SignatureValidator($secret);

    expect($validator->isValidV3($method, $uri, $body, $signature, $timestamp, 300, $nowMs))->toBeTrue();
});

it('rejects a v3 signature with a tampered body', function (): void {
    $secret = 'test-secret';
    $method = 'POST';
    $uri = 'https://example.com/webhook';
    $body = '{"event":"contact.created"}';
    $timestamp = '1700000000000';
    $nowMs = 1700000100000;

    $source = $method.$uri.$body.$timestamp;
    $signature = base64_encode(hash_hmac('sha256', $source, $secret, true));

    $validator = new SignatureValidator($secret);
    $tamperedBody = '{"event":"contact.deleted"}';

    expect($validator->isValidV3($method, $uri, $tamperedBody, $signature, $timestamp, 300, $nowMs))->toBeFalse();
});

it('rejects a v3 signature whose timestamp exceeds maxAgeSeconds', function (): void {
    $secret = 'test-secret';
    $method = 'POST';
    $uri = 'https://example.com/webhook';
    $body = '{"event":"contact.created"}';
    $timestamp = '1700000000000';
    // 600 001ms after timestamp; 300s maxAge means 300 000ms allowed
    $nowMs = 1700000600001;

    $source = $method.$uri.$body.$timestamp;
    $signature = base64_encode(hash_hmac('sha256', $source, $secret, true));

    $validator = new SignatureValidator($secret);

    expect($validator->isValidV3($method, $uri, $body, $signature, $timestamp, 300, $nowMs))->toBeFalse();
});

// v2 tests

it('validates a correct v2 signature', function (): void {
    $secret = 'test-secret';
    $method = 'POST';
    $uri = 'https://example.com/webhook';
    $body = '{"event":"contact.created"}';

    $source = $secret.$method.$uri.$body;
    $signature = hash('sha256', $source);

    $validator = new SignatureValidator($secret);

    expect($validator->isValidV2($method, $uri, $body, $signature))->toBeTrue();
});

it('rejects a v2 signature with a tampered body', function (): void {
    $secret = 'test-secret';
    $method = 'POST';
    $uri = 'https://example.com/webhook';
    $body = '{"event":"contact.created"}';

    $source = $secret.$method.$uri.$body;
    $signature = hash('sha256', $source);

    $validator = new SignatureValidator($secret);
    $tamperedBody = '{"event":"contact.deleted"}';

    expect($validator->isValidV2($method, $uri, $tamperedBody, $signature))->toBeFalse();
});
