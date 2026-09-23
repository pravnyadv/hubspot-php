<?php

declare(strict_types=1);

namespace HubSpot\Exceptions;

/**
 * One entry of a HubSpot error's `errors` array, usually a single invalid
 * field or parameter.
 */
final readonly class ErrorDetail
{
    /** @param  array<string, mixed>  $context */
    public function __construct(
        public string $message,
        public ?string $code = null,
        public ?string $in = null,
        public ?string $subCategory = null,
        public array $context = [],
    ) {}
}
