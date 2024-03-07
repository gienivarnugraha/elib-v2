<?php

namespace App\Core\Application\Menu;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class Manager
{
    /**
     * Hold the main menu items
     */
    protected array $items = [];

    /**
     * Register menu item(s)
     */
    public function register(Item|array $items): static
    {
        foreach (Arr::wrap($items) as $item) {
            $this->registerItem($item);
        }

        return $this;
    }

    /**
     * Register a single menu item
     */
    public function registerItem(Item $item): static
    {
        throw_if(array_key_exists($item->id, $this->items), "Same id of menu found for {$item->id}");

        $this->items[] = $item;

        return $this;
    }

    /**
     * Get all registered menu items
     */
    public function get(): Collection
    {
        $items = (new Collection($this->items))->map(fn ($item) => $this->checkQuickCreateProperties($item));

        $ordered = $this->checkPositions($items);

        return $ordered->filter->authorizedToSee()->values();
    }

    /**
     * Clears all menu items
     */
    public function clear(): static
    {
        $this->items = [];

        return $this;
    }

    /**
     * Check quick create properties and add default props
     */
    protected function checkQuickCreateProperties(Item $item): Item
    {
        if ($item->inQuickCreate) {
            if (! $item->quickCreateRoute) {
                $item->quickCreateRoute(rtrim($item->route, '/').'/'.'create');
            }

            if (! $item->quickCreateName) {
                $item->quickCreateName($item->singularName ?? $item->name);
            }
        }

        return $item;
    }

    /**
     * Check if order is set and sort the items
     */
    protected function checkPositions(Collection $items): Collection
    {
        /**
         * If there is no position set, add the index + 5
         */
        $items->each(function ($item, $index) {
            if (! $item->position) {
                $item->position($index + 10);
            }
        });

        /**
         * Sort the items with the actual order
         */
        return $this->sort($items);
    }

    /**
     * Sort the items
     */
    protected function sort(Collection $items): Collection
    {
        return $items->sort(function ($a, $b) {
            if ($a->position == $b->position) {
                return 0;
            }

            return ($a->position < $b->position) ? -1 : 1;
        })->values();
    }
}
