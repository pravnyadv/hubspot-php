<?php

declare(strict_types=1);

namespace HubSpot\Exceptions;

/**
 * HubSpot's error body. Every API family's spec defines the same `Error`
 * schema, so one shape covers every failure the API reports.
 */
final readonly class HubSpotError
{
    /**
     * @param  array<string, mixed>  $context  e.g. ['missingScopes' => ['crm.objects.contacts.read']]
     * @param  array<string, string>  $links
     * @param  list<ErrorDetail>  $errors
     */
    public function __construct(
        public string $message,
        public ?string $category = null,
        public ?string $subCategory = null,
        public ?string $correlationId = null,
        public array $context = [],
        public array $links = [],
        public array $errors = [],
    ) {}

    /**
     * Null when the body is not a HubSpot error, e.g. an HTML 502 from the edge.
     *
     * @param  array<mixed>|null  $body
     */
    public static function fromBody(?array $body): ?self
    {
        if ($body === null || ! is_string($body['message'] ?? null)) {
            return null;
        }

        $details = [];
        foreach (is_array($body['errors'] ?? null) ? $body['errors'] : [] as $error) {
            if (is_array($error) && is_string($error['message'] ?? null)) {
                $details[] = new ErrorDetail(
                    $error['message'],
                    self::string($error, 'code'),
                    self::string($error, 'in'),
                    self::string($error, 'subCategory'),
                    self::map($error, 'context'),
                );
            }
        }

        return new self(
            $body['message'],
            self::string($body, 'category'),
            self::string($body, 'subCategory'),
            self::string($body, 'correlationId'),
            self::map($body, 'context'),
            array_filter(self::map($body, 'links'), is_string(...)),
            $details,
        );
    }

    /** @param  array<mixed>  $data */
    private static function string(array $data, string $key): ?string
    {
        return is_string($data[$key] ?? null) ? $data[$key] : null;
    }

    /**
     * @param  array<mixed>  $data
     * @return array<string, mixed>
     */
    private static function map(array $data, string $key): array
    {
        return is_array($data[$key] ?? null) ? $data[$key] : [];
    }
}
