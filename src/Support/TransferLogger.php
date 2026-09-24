<?php

declare(strict_types=1);

namespace HubSpot\Support;

use GuzzleHttp\TransferStats;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Throwable;

/**
 * Per-attempt request logging shared by Client and OAuthClient: one line per
 * attempt (retries included), debug on success, warning on a dropped connection/
 * 429/5xx, info on any other 4xx. Bodies, query strings, and the token are never
 * logged.
 */
final class TransferLogger
{
    /** @param  array<string, mixed>  $context  the caller's own context (e.g. portal_id) */
    public static function log(LoggerInterface $logger, array $context, TransferStats $stats): void
    {
        $request = $stats->getRequest();
        $response = $stats->getResponse();
        $status = $response?->getStatusCode();
        $path = $request->getUri()->getPath();
        $error = $stats->getHandlerErrorData();

        $logContext = self::requestContext($context, $request->getMethod(), $path, $response) + array_filter([
            'status' => $status,
            'duration_ms' => $stats->getTransferTime() !== null ? (int) round($stats->getTransferTime() * 1000) : null,
            'error' => $error instanceof Throwable ? $error->getMessage() : null,
        ], static fn (mixed $value): bool => $value !== null);

        $level = match (true) {
            $status === null, $status === 429, $status >= 500 => LogLevel::WARNING,
            $status >= 400 => LogLevel::INFO,
            default => LogLevel::DEBUG,
        };

        $logger->log($level, sprintf('HubSpot %s %s %s', $request->getMethod(), $path, $status ?? 'no response'), $logContext);
    }

    /**
     * The caller's context plus what identifies this request — also used directly
     * by Client when building an ApiException's context, not just for logging.
     * Query strings are left out: they can carry emails and other personal data.
     *
     * @param  array<string, mixed>  $context
     * @return array<string, mixed>
     */
    public static function requestContext(array $context, string $method, string $path, ?ResponseInterface $response = null): array
    {
        return $context + array_filter([
            'method' => $method,
            'path' => '/'.ltrim(strtok($path, '?') ?: '', '/'),
            'correlation_id' => $response?->getHeaderLine('X-HubSpot-Correlation-Id') ?: null,
        ], static fn (mixed $value): bool => $value !== null);
    }
}
