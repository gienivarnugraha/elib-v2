<?php

namespace App\Core\Application\Table\Columns;

class BelongsToColumn extends RelationshipColumn
{
    //public string $component = 'v-belongs-to-column';

    /**
     * @var callable|null
     */
    public $orderColumnCallback;

    /**
     * Add custom order column name callback
     */
    public function orderByColumn(callable $callback): static
    {
        $this->orderColumnCallback = $callback;

        return $this;
    }
}
