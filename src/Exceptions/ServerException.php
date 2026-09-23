<?php

declare(strict_types=1);

namespace HubSpot\Exceptions;

/** A 5xx after retries were exhausted (idempotent methods) or on the first attempt (POST/PATCH). */
final class ServerException extends ApiException {}
