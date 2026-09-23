<?php

declare(strict_types=1);

namespace HubSpot;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Promise\Each;
use GuzzleHttp\Promise\PromiseInterface;
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
use HubSpot\Exceptions\RateLimitException;
use HubSpot\Middleware\RetryMiddleware;
use HubSpot\Resources\AccountInfo;
use HubSpot\Resources\Files;
use HubSpot\Resources\Scheduler;
use Psr\Http\Message\ResponseInterface;

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

    public function __construct(
        private readonly AuthProvider $auth,
        ?ClientInterface $http = null,
        private readonly string $version = self::DEFAULT_VERSION,
        int $maxRetries = 3,
        private readonly ResponseFormat $responseFormat = ResponseFormat::Object,
    ) {
        $this->http = $http ?? self::defaultHttpClient($maxRetries);
    }

    public static function defaultHttpClient(int $maxRetries = 3): GuzzleClient
    {
        $stack = HandlerStack::create();
        $stack->push(RetryMiddleware::create($maxRetries));

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
            // http_errors is off, so a GuzzleException here is a transport
            // failure (DNS, connection), not an HTTP status.
            throw new ApiException(0, null, 'HubSpot request failed: '.$e->getMessage(), $e);
        }

        return $this->ensureSuccessful($response);
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
        return $this->http->requestAsync($method, ltrim($path, '/'), $this->prepareOptions($options))
            ->then(fn (ResponseInterface $response): ResponseInterface => $this->ensureSuccessful($response));
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

        return $options;
    }

    private function ensureSuccessful(ResponseInterface $response): ResponseInterface
    {
        $status = $response->getStatusCode();
        if ($status >= 400) {
            throw $this->mapError($status, $response);
        }

        return $response;
    }

    private function mapError(int $status, ResponseInterface $response): ApiException
    {
        $raw = (string) $response->getBody();

        if ($status === 429) {
            $body = ApiException::decodeBody($raw);
            $retryAfter = $response->hasHeader('Retry-After')
                ? (int) $response->getHeaderLine('Retry-After')
                : null;

            return new RateLimitException(
                $retryAfter,
                $status,
                $body,
                ApiException::messageFrom($body) ?? "HubSpot rate limit hit (HTTP {$status})",
            );
        }

        return ApiException::fromResponse($status, $raw);
    }

    /** @return array<mixed>|object */
    private function decode(ResponseInterface $response, ResponseFormat $format): array|object
    {
        $body = (string) $response->getBody();

        return $body === '' ? $format->empty() : $format->decode($body);
    }
}
