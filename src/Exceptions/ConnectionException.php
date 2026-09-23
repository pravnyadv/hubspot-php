<?php

declare(strict_types=1);

namespace HubSpot\Exceptions;

/** No response at all: DNS, connection refused, timeout. Status is 0. */
final class ConnectionException extends ApiException {}
