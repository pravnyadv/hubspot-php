<?php

declare(strict_types=1);

namespace HubSpot\Pagination;

use Closure;
use Generator;
use IteratorAggregate;

/**
 * Walks HubSpot's cursor pagination (`paging.next.after`) transparently: give
 * it a callback that fetches one page for a given cursor, and iterate every
 * record across all pages. One abstraction serves owners, list memberships,
 * forms, and CMS pages, which all share this shape.
 *
 * @implements IteratorAggregate<int, mixed>
 */
final class Paginator implements IteratorAggregate
{
    /** @param  Closure(?string): (array<mixed>|object)  $fetchPage */
    private function __construct(
        private readonly Closure $fetchPage,
        private readonly string $resultsKey = 'results',
    ) {}

    /** @param  callable(?string): (array<mixed>|object)  $fetchPage */
    public static function make(callable $fetchPage, string $resultsKey = 'results'): self
    {
        return new self($fetchPage(...), $resultsKey);
    }

    public function getIterator(): Generator
    {
        $after = null;

        do {
            $page = ($this->fetchPage)($after);

            foreach (self::get($page, $this->resultsKey) ?? [] as $item) {
                yield $item;
            }

            $next = self::get(self::get($page, 'paging'), 'next');
            $after = self::get($next, 'after');
        } while ($after !== null && $after !== '');
    }

    /**
     * Read one key from a page whether it decoded to an array or a stdClass, so
     * the cursor walk works in either response format.
     *
     * @param  array<mixed>|object|null  $data
     */
    private static function get(array|object|null $data, string $key): mixed
    {
        if (is_object($data)) {
            return $data->{$key} ?? null;
        }

        return $data[$key] ?? null;
    }

    /** @return list<mixed> */
    public function all(): array
    {
        return iterator_to_array($this->getIterator(), false);
    }
}
