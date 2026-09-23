<?php

declare(strict_types=1);

namespace HubSpot;

/**
 * How decoded JSON responses are handed back. Set once on the client, or
 * override per call. No typed models: this is a plain json_decode flag, so a
 * response is either an associative array or a stdClass tree, nothing to learn
 * or maintain per endpoint.
 */
enum ResponseFormat
{
    /** Associative arrays: $contact['properties']['email']. */
    case Assoc;

    /** stdClass trees: $contact->properties->email. */
    case Object;

    /** @return array<mixed>|object */
    public function decode(string $json): array|object
    {
        $decoded = json_decode($json, $this === self::Assoc);

        if ($this === self::Object) {
            return is_object($decoded) ? $decoded : new \stdClass;
        }

        return is_array($decoded) ? $decoded : [];
    }

    /** @return array<mixed>|object */
    public function empty(): array|object
    {
        return $this === self::Object ? new \stdClass : [];
    }
}
