<?php

declare(strict_types=1);

namespace HubSpot\Resources;

final class BlogAuthors extends CrudResource
{
    protected function base(): string
    {
        return "/cms/blogs/{$this->version()}/authors";
    }

    /** @param  list<mixed>  $inputs */
    public function batchArchive(array $inputs): void
    {
        $this->batch("{$this->base()}/batch/archive", $inputs);
    }

    /**
     * @param  list<mixed>  $inputs
     * @return array<mixed>|object
     */
    public function batchCreate(array $inputs): array|object
    {
        return $this->batch("{$this->base()}/batch/create", $inputs);
    }

    /**
     * @param  list<mixed>  $inputs
     * @return array<mixed>|object
     */
    public function batchRead(array $inputs): array|object
    {
        return $this->batch("{$this->base()}/batch/read", $inputs);
    }

    /**
     * @param  list<mixed>  $inputs
     * @return array<mixed>|object
     */
    public function batchUpdate(array $inputs): array|object
    {
        return $this->batch("{$this->base()}/batch/update", $inputs);
    }
}
