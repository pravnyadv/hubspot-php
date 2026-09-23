<?php

declare(strict_types=1);

namespace HubSpot\Exceptions;

/** A 400 or 422: HubSpot rejected the input. Field-level detail is in $error->errors. */
final class ValidationException extends ApiException {}
