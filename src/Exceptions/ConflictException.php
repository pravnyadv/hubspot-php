<?php

declare(strict_types=1);

namespace HubSpot\Exceptions;

/**
 * A 409, e.g. creating a contact whose email already exists.
 */
final class ConflictException extends ApiException
{
    /**
     * The id of the record that already exists, when HubSpot names it
     * ("Contact already exists. Existing ID: 123"). Read from the message
     * text, so null if HubSpot words it differently.
     */
    public function existingId(): ?string
    {
        return preg_match('/Existing ID:\s*(\d+)/i', $this->error->message ?? '', $matches) === 1 ? $matches[1] : null;
    }
}
