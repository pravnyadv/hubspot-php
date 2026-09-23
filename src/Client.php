<?php

declare(strict_types=1);

namespace HubSpot;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Promise\Each;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\TransferStats;
use HubSpot\Api\AutomationApi;
use HubSpot\Api\CmsApi;
use HubSpot\Api\CommerceApi;
use HubSpot\Api\ConversationsApi;
use HubSpot\Api\CrmApi;
use HubSpot\Api\EventsApi;
use HubSpot\Api\MarketingApi;
use HubSpot\Api\SettingsApi;
use HubSpot\Contracts\AuthProvider;
use HubSpot\Exceptions\ApiException;
use HubSpot\Exceptions\ConnectionException;
use HubSpot\Middleware\RetryMiddleware;
use HubSpot\Resources\AccountInfo;
use HubSpot\Resources\Files;
use HubSpot\Resources\Scheduler;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Throwable;

/**
 * The HTTP data-plane transport: attaches the bearer token, retries transient
 * failures, maps error responses to typed exceptions, and returns plain decoded
 * arrays. Resources own their own paths (including the date-based version
 * segment); this class only knows how to send a request and read the response.
 */
final class Client
{
    /**
     * Default date-based version for the CRM/account/CMS families. Not global:
     * OAuth uses 2026-03 and Marketing Forms 2026-09-beta, each set on its own
     * resource, because HubSpot versions API families independently.
     */
    public const DEFAULT_VERSION = '2026-09';

    private const BASE_URI = 'https://api.hubapi.com';

    private readonly ClientInterface $http;

    /** @var array<string, mixed> */
    private array $context;

    /**
     * @param  array<string, mixed>  $context  added to every log line and exception, e.g. ['request_id' => ..., 'portal_id' => ...]
     * @param  array<int|string, callable>  $middleware  Guzzle middleware for the default stack (string keys name them)
     */
    public function __construct(
        private readonly AuthProvider $auth,
        ?ClientInterface $http = null,
        private readonly string $version = self::DEFAULT_VERSION,
        int $maxRetries = 3,
        private readonly ResponseFormat $responseFormat = ResponseFormat::Object,
        array $context = [],
        private readonly ?LoggerInterface $logger = null,
        array $middleware = [],
    ) {
        if ($http !== null && $middleware !== []) {
            throw new InvalidArgumentException('Pass either middleware or a custom http client; push the middleware onto your own client\'s handler stack instead.');
        }

        $this->context = $context;
        $this->http = $http ?? self::defaultHttpClient($maxRetries, $middleware);
    }

    /** @param  array<int|string, callable>  $middleware */
    public static function defaultHttpClient(int $maxRetries = 3, array $middleware = []): GuzzleClient
    {
        $stack = HandlerStack::create();
        $stack->push(RetryMiddleware::create($maxRetries));

        // Inside the retry middleware, so each runs once per attempt: a rate
        // limiter counts every request HubSpot actually receives.
        foreach ($middleware as $name => $fn) {
            $stack->push($fn, is_string($name) ? $name : '');
        }

        return new GuzzleClient([
            'base_uri' => self::BASE_URI,
            'handler' => $stack,
            // Errors are mapped to typed exceptions in send(), after the retry
            // middleware has inspected the response status, so Guzzle must not
            // throw on 4xx/5xx itself.
            'http_errors' => false,
        ]);
    }

    public function version(): string
    {
        return $this->version;
    }

    /**
     * A copy whose logs and exceptions also carry $context (e.g. a job's ids),
     * sharing this client's auth and HTTP connection.
     *
     * @param  array<string, mixed>  $context
     */
    public function withContext(array $context): self
    {
        $clone = clone $this;
        $clone->context = $context + $this->context;

        return $clone;
    }

    public function __clone()
    {
        // Resource groups hold the client that built them, so a copy builds its own.
        $this->crm = $this->cms = $this->marketing = $this->commerce = null;
        $this->events = $this->settings = $this->automation = $this->conversations = null;
    }

    /**
     * Send a request and return the raw PSR-7 response. The escape hatch for
     * non-JSON endpoints (e.g. CMS source-code file downloads).
     *
     * @param  array<string, mixed>  $options  Guzzle request options.
     */
    public function send(string $method, string $path, array $options = []): ResponseInterface
    {
        try {
            $response = $this->http->request($method, ltrim($path, '/'), $this->prepareOptions($options));
        } catch (GuzzleException $e) {
            throw $this->mapFailure($e, $method, $path);
        }

        return $this->ensureSuccessful($response, $method, $path);
    }

    /**
     * Async counterpart of send(): returns a promise that resolves to the raw
     * response, or rejects with the same typed exceptions as send(). Use with
     * GuzzleHttp\Promise\Utils::all()/settle() (or Pool) to fan out concurrent
     * requests, e.g. several timeline batches at once.
     *
     * @param  array<string, mixed>  $options  Guzzle request options.
     */
    public function sendAsync(string $method, string $path, array $options = []): PromiseInterface
    {
        return $this->http->requestAsync($method, ltrim($path, '/'), $this->prepareOptions($options))->then(
            fn (ResponseInterface $response): ResponseInterface => $this->ensureSuccessful($response, $method, $path),
            fn (Throwable $reason) => throw $this->mapFailure($reason, $method, $path),
        );
    }

    public function responseFormat(): ResponseFormat
    {
        return $this->responseFormat;
    }

    /**
     * Async counterpart of request(): resolves to the decoded body.
     *
     * @param  array<string, mixed>  $options  Guzzle request options.
     */
    public function requestAsync(string $method, string $path, array $options = [], ?ResponseFormat $format = null): PromiseInterface
    {
        $format ??= $this->responseFormat;

        return $this->sendAsync($method, $path, $options)
            ->then(fn (ResponseInterface $response): array|object => $this->decode($response, $format));
    }

    /**
     * Run promise-returning callables with capped concurrency, collecting every
     * outcome keyed by the input key. A fulfilled entry is the resolved value; a
     * rejected one is the caught Throwable, so per-request results can be
     * inspected (like Utils::settle, but bounded so a large fan-out does not
     * open hundreds of connections at once).
     *
     * @param  iterable<int|string, callable(): PromiseInterface>  $thunks
     * @return array<int|string, mixed>
     */
    public function pool(iterable $thunks, int $concurrency = 5): array
    {
        $results = [];

        Each::ofLimit(
            (static function () use ($thunks): \Generator {
                foreach ($thunks as $key => $thunk) {
                    yield $key => $thunk();
                }
            })(),
            $concurrency,
            static function (mixed $value, int|string $key) use (&$results): void {
                $results[$key] = $value;
            },
            static function (mixed $reason, int|string $key) use (&$results): void {
                $results[$key] = $reason;
            },
        )->wait();

        return $results;
    }

    /**
     * Send a request and return HubSpot's decoded JSON body, as an associative
     * array or a stdClass tree per the client's response format (override it for
     * this one call with $format).
     *
     * @param  array<string, mixed>  $options  Guzzle request options.
     * @return array<mixed>|object
     */
    public function request(string $method, string $path, array $options = [], ?ResponseFormat $format = null): array|object
    {
        return $this->decode($this->send($method, $path, $options), $format ?? $this->responseFormat);
    }

    // Resources are grouped by HubSpot product area. Each group owns its own
    // resource classes; this client only knows the groups. The per-resource
    // version override lives on each group's factory, e.g.
    // $client->crm()->owners('2026-03'). Group objects are stateless and reused.

    private ?CrmApi $crm = null;

    private ?CmsApi $cms = null;

    private ?MarketingApi $marketing = null;

    private ?CommerceApi $commerce = null;

    private ?EventsApi $events = null;

    private ?SettingsApi $settings = null;

    private ?AutomationApi $automation = null;

    private ?ConversationsApi $conversations = null;

    public function crm(): CrmApi
    {
        return $this->crm ??= new CrmApi($this);
    }

    public function cms(): CmsApi
    {
        return $this->cms ??= new CmsApi($this);
    }

    public function marketing(): MarketingApi
    {
        return $this->marketing ??= new MarketingApi($this);
    }

    public function commerce(): CommerceApi
    {
        return $this->commerce ??= new CommerceApi($this);
    }

    public function events(): EventsApi
    {
        return $this->events ??= new EventsApi($this);
    }

    public function settings(): SettingsApi
    {
        return $this->settings ??= new SettingsApi($this);
    }

    public function automation(): AutomationApi
    {
        return $this->automation ??= new AutomationApi($this);
    }

    public function conversations(): ConversationsApi
    {
        return $this->conversations ??= new ConversationsApi($this);
    }

    // Single-endpoint areas HubSpot does not group; the resource is returned
    // directly, e.g. $client->account()->details(), with the same version override.

    public function account(?string $version = null): AccountInfo
    {
        return new AccountInfo($this, $version);
    }

    public function files(?string $version = null): Files
    {
        return new Files($this, $version);
    }

    public function scheduler(?string $version = null): Scheduler
    {
        return new Scheduler($this, $version);
    }

    /**
     * Attach auth and normalise options for both sync and async paths.
     *
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    private function prepareOptions(array $options): array
    {
        $options['headers']['Authorization'] = 'Bearer '.$this->auth->accessToken();
        $options['headers']['Accept'] ??= 'application/json';

        // HubSpot expects boolean query params as true/false, not PHP's default 1/0.
        if (isset($options['query']) && is_array($options['query'])) {
            $options['query'] = array_map(
                static fn ($value) => is_bool($value) ? ($value ? 'true' : 'false') : $value,
                $options['query'],
            );
        }

        // on_stats fires once per attempt, retries included, with timing.
        if ($this->logger !== null) {
            $callerStats = $options['on_stats'] ?? null;
            $options['on_stats'] = function (TransferStats $stats) use ($callerStats): void {
                $this->logTransfer($stats);
                if (is_callable($callerStats)) {
                    $callerStats($stats);
                }
            };
        }

        return $options;
    }

    private function logTransfer(TransferStats $stats): void
    {
        $request = $stats->getRequest();
        $response = $stats->getResponse();
        $status = $response?->getStatusCode();
        $path = $request->getUri()->getPath();
        $error = $stats->getHandlerErrorData();

        $context = $this->requestContext($request->getMethod(), $path, $response) + array_filter([
            'status' => $status,
            'duration_ms' => $stats->getTransferTime() !== null ? (int) round($stats->getTransferTime() * 1000) : null,
            'error' => $error instanceof Throwable ? $error->getMessage() : null,
        ], static fn (mixed $value): bool => $value !== null);

        $level = match (true) {
            $status === null, $status === 429, $status >= 500 => LogLevel::WARNING,
            $status >= 400 => LogLevel::INFO,
            default => LogLevel::DEBUG,
        };

        $this->logger?->log($level, sprintf('HubSpot %s %s %s', $request->getMethod(), $path, $status ?? 'no response'), $context);
    }

    /**
     * The client's context plus what identifies this request. Query strings are
     * left out: they can carry emails and other personal data.
     *
     * @return array<string, mixed>
     */
    private function requestContext(string $method, string $path, ?ResponseInterface $response = null): array
    {
        return $this->context + array_filter([
            'method' => $method,
            'path' => '/'.ltrim(strtok($path, '?') ?: '', '/'),
            'correlation_id' => $response?->getHeaderLine('X-HubSpot-Correlation-Id') ?: null,
        ], static fn (mixed $value): bool => $value !== null);
    }

    private function ensureSuccessful(ResponseInterface $response, string $method, string $path): ResponseInterface
    {
        if ($response->getStatusCode() >= 400) {
            throw $this->mapError($response, $method, $path);
        }

        return $response;
    }

    private function mapError(ResponseInterface $response, string $method, string $path): ApiException
    {
        return ApiException::fromResponse(
            $response->getStatusCode(),
            (string) $response->getBody(),
            $this->requestContext($method, $path, $response),
            $response->hasHeader('Retry-After') ? (int) $response->getHeaderLine('Retry-After') : null,
        );
    }

    /**
     * Guzzle's own exceptions become ours. Anything else (e.g. an app
     * middleware's rate-limit exception) passes through untouched.
     */
    private function mapFailure(Throwable $e, string $method, string $path): Throwable
    {
        // Only a caller-supplied client with http_errors on throws for a status.
        if ($e instanceof BadResponseException) {
            return $this->mapError($e->getResponse(), $method, $path);
        }

        if ($e instanceof GuzzleException) {
            return new ConnectionException(0, null, 'HubSpot request failed: '.$e->getMessage(), $this->requestContext($method, $path), $e);
        }

        return $e;
    }

    /** @return array<mixed>|object */
    private function decode(ResponseInterface $response, ResponseFormat $format): array|object
    {
        $body = (string) $response->getBody();

        return $body === '' ? $format->empty() : $format->decode($body);
    }
}
