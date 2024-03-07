<?php

namespace App\Core\Traits;

use Illuminate\Support\Collection;

trait ResolvesFilters
{
    /**
     * Get the available filters for the user
     */
    public function resolveFilters(): Collection
    {
        $filters = $this->filters();

        $collection = is_array($filters) ? new Collection($filters) : $filters;

        return $collection->filter->authorizedToSee()->values();
    }

    /**
     * @codeCoverageIgnore
     *
     * Get the defined filters
     */
    public function filters(): array|Collection
    {
        return [];
    }
}
