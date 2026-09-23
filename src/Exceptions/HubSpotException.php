<?php

declare(strict_types=1);

namespace HubSpot\Exceptions;

use RuntimeException;

/**
 * Base for every exception this package throws. Catch this to catch anything
 * from the library.
 */
class HubSpotException extends RuntimeException {}
