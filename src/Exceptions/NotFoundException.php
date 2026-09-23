<?php

declare(strict_types=1);

namespace HubSpot\Exceptions;

/** A 404. Resource find() methods turn this into null. */
final class NotFoundException extends ApiException {}
