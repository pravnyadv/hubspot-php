<?php

declare(strict_types=1);

namespace HubSpot\Search;

// Groups are OR-ed together; filters within a group are AND-ed (HubSpot PublicObjectSearchRequest).
final class SearchRequest
{
    /** @var list<list<array<string, mixed>>> */
    private array $filterGroups = [];

    /** @var list<string> */
    private array $properties = [];

    /** @var list<array<string, string>> */
    private array $sorts = [];

    private ?int $limit = null;

    private ?string $after = null;

    private ?string $query = null;

    public static function create(): self
    {
        return new self;
    }

    /**
     * Appends a filter to the current group, creating the first group lazily.
     * Pass null for $value with valueless operators such as HAS_PROPERTY.
     */
    public function where(string $propertyName, string $operator, mixed $value = null): self
    {
        if ($this->filterGroups === []) {
            $this->filterGroups[] = [];
        }

        /** @var array<string, mixed> $filter */
        $filter = ['propertyName' => $propertyName, 'operator' => $operator];
        if ($value !== null) {
            $filter['value'] = $value;
        }

        $this->filterGroups[count($this->filterGroups) - 1][] = $filter;

        return $this;
    }

    /** Starts a new filter group; groups are OR-ed, filters within a group are AND-ed. */
    public function orGroup(): self
    {
        $this->filterGroups[] = [];

        return $this;
    }

    /**
     * @param  list<string>  $properties
     */
    public function properties(array $properties): self
    {
        $this->properties = $properties;

        return $this;
    }

    public function sort(string $propertyName, string $direction = 'DESCENDING'): self
    {
        $this->sorts[] = ['propertyName' => $propertyName, 'direction' => $direction];

        return $this;
    }

    public function limit(int $limit): self
    {
        $this->limit = $limit;

        return $this;
    }

    public function after(string $after): self
    {
        $this->after = $after;

        return $this;
    }

    public function query(string $query): self
    {
        $this->query = $query;

        return $this;
    }

    /**
     * Assembles the HubSpot PublicObjectSearchRequest body, omitting any empty or null parts.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        /** @var array<string, mixed> $body */
        $body = [];

        if ($this->filterGroups !== []) {
            $body['filterGroups'] = array_map(
                /** @param list<array<string, mixed>> $filters @return array{filters: list<array<string, mixed>>} */
                static fn (array $filters): array => ['filters' => $filters],
                $this->filterGroups,
            );
        }

        if ($this->properties !== []) {
            $body['properties'] = $this->properties;
        }

        if ($this->sorts !== []) {
            $body['sorts'] = $this->sorts;
        }

        if ($this->limit !== null) {
            $body['limit'] = $this->limit;
        }

        if ($this->after !== null) {
            $body['after'] = $this->after;
        }

        if ($this->query !== null) {
            $body['query'] = $this->query;
        }

        return $body;
    }
}
