<?php

namespace App\Core\Application\Table;

use App\Core\Application\Table\Columns\ActionColumn;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Collection;
use JsonSerializable;

class TableSettings implements Arrayable, JsonSerializable
{
    /**
     * Holds the partially user meta key for the saved table settings
     */
    protected string $meta = 'table-settings';

    /**
     * @var \Illumiante\Support\Collection
     */
    protected $columns;

    /**
     * @var \Illumiante\Support\Collection
     */
    protected $filters;

    /**
     * TableSettings Construct
     *
     * @param  \App\Core\Table\Table  $table
     * @param  \App\Core\Contracts\Metable  $user The user the data/settings are intended for
     */
    public function __construct(protected Table $table) // protected Metable $user
    {
    }

    /**
     * Table actions
     *
     * The function removes also the actions that are hidden on INDEX
     */
    public function actions(): Collection
    {
        return $this->table->resolveActions()
            ->reject(fn ($action) => $action->hideOnIndex === true)
            ->values();
    }

    /**
     * Get the available table saved filters
     */
    public function filters(): Collection
    {
        return $this->table->resolveFilters()->sortBy('name')->values();
    }

    /**
     * Get the table max height
     */
    public function maxHeight(): float|int|string|null
    {
        return $this->table->maxHeight;
    }

    /**
     * Get the table per page
     */
    public function perPage(): int
    {
        return $this->table->perPage;
    }

    /**
     * Get the user columns meta name
     */
    public function getMetaName(): string
    {
        return $this->meta . '-' . $this->table->identifier();
    }

    /**
     * Get table order, checks for custom ordering too
     */
    public function getOrder(): array
    {
        return $this->table->order;
    }

    /**
     * Get the actual table columns that should be shown to the user
     */
    public function getColumns(): Collection
    {
        if ($this->columns) {
            return $this->columns;
        }

        $availableColumns = $this->table->getColumns()->filter->authorizedToSee()->values();

        // Merge the order and the visibility and all columns so we can filter them later
        $columns = $availableColumns->map(function ($column, $index) {
            if ($column instanceof ActionColumn) {
                $column->order(1000)->hidden(false);
            }

            return $column;
        });

        return $this->columns = $columns->sortBy('order')->values();
    }

    /**
     * Guards the not sortable columns from mole changes via API
     *
     * @param  array  $payload
     */
    protected function guardNotSortableColumns(&$payload): void
    {
        // Protected the not sortable columns
        // E.q. if column is marked to be not sortable
        // The user is not allowed to change to sortable
        foreach ($payload['order'] as $key => $sort) {
            $column = $this->table->getColumn($sort['attribute']);

            // Reset with the default attributes for additional protection
            if (!$column->isSortable()) {
                unset($payload['order'][$key]);
            }
        }
    }

    /**
     * toArray
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'identifier' => $this->table->identifier(),
            'perPage' => $this->perPage(),
            'requestQueryString' => $this->table->getRequestQueryString(),
            'maxHeight' => $this->maxHeight(),
            'filters' => $this->filters(),
            'columns' => $this->getColumns(),
            'order' => $this->getOrder(),
            // 'actions' => $this->actions(),
        ];
    }

    /**
     * jsonSerialize
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
