<?php

declare(strict_types=1);

namespace HubSpot\Resources;

use HubSpot\Pagination\Paginator;

/**
 * Sequences: automated series of timed sales emails and tasks.
 * Covers sequence definitions, enrollments, and service-account-scoped operations.
 */
final class Sequences extends Resource
{
    private function base(): string
    {
        return "/automation/sequences/{$this->version()}";
    }

    // ── Sequence definitions ─────────────────────────────────────────────────

    /**
     * @param  array<string, mixed>  $query
     * @return array<mixed>|object
     */
    public function list(array $query = []): array|object
    {
        return $this->client->request('GET', $this->base(), [
            'query' => $this->filterNull($query),
        ]);
    }

    public function all(): Paginator
    {
        return Paginator::make(fn (?string $after) => $this->list(['after' => $after]));
    }

    /** @return array<mixed>|object */
    public function get(string $sequenceId): array|object
    {
        return $this->client->request('GET', "{$this->base()}/{$sequenceId}");
    }

    // ── Enrollments ──────────────────────────────────────────────────────────

    // No listEnrollments(): "{base}/enrollments" has exactly one operation
    // (POST, "Enroll a contact"), already covered by enroll() below. There is
    // no listing/search endpoint for enrollments anywhere in the spec.

    /**
     * Enroll a contact in a sequence.
     *
     * @param  array<string, mixed>  $body
     * @return array<mixed>|object
     */
    public function enroll(array $body): array|object
    {
        return $this->client->request('POST', "{$this->base()}/enrollments", [
            'json' => $body,
        ]);
    }

    /** @return array<mixed>|object */
    public function contactEnrollments(string $contactId): array|object
    {
        return $this->client->request('GET', "{$this->base()}/enrollments/contact/{$contactId}");
    }

    // ── Service-account-scoped ────────────────────────────────────────────────

    /**
     * List sequences visible to the service account associated with the token.
     *
     * @param  array<string, mixed>  $query
     * @return array<mixed>|object
     */
    public function serviceAccountSequences(array $query = []): array|object
    {
        return $this->client->request('GET', "{$this->base()}/serviceaccounts/sequences", [
            'query' => $this->filterNull($query),
        ]);
    }

    /** @return array<mixed>|object */
    public function serviceAccountSequence(string $sequenceId): array|object
    {
        return $this->client->request('GET', "{$this->base()}/serviceaccounts/sequences/{$sequenceId}");
    }

    /** @return array<mixed>|object */
    public function performance(string $sequenceId): array|object
    {
        return $this->client->request('GET', "{$this->base()}/serviceaccounts/sequences/{$sequenceId}/performance");
    }

    // No serviceAccountEnrollments(): "{base}/serviceaccounts/enrollments" has
    // exactly one operation (POST, "Enroll Service Account"), an enrollment
    // create, not a listing/search endpoint. No such read endpoint exists.
}
