<?php

namespace App\Core\Application\Fields\Traits;

use App\Core\Application\Table\Column;

trait DisplaysOnIndex
{
    /**
     * @var callable[]
     */
    public array $tapIndexColumnCallbacks = [];

    /**
     * @var callable
     */
    public $indexColumnCallback;

    /**
     * Provide the column used for index
     *
     * @return \App\Core\Table\Column
     */
    public function indexColumn(): ?Column
    {
        return new Column($this->attribute, $this->label);
    }

    /**
     * Add custom index column resolver callback
     */
    public function swapIndexColumn(callable $callback): static
    {
        $this->indexColumnCallback = $callback;

        return $this;
    }

    /**
     * Tap the index column
     */
    public function tapIndexColumn(callable $callback): static
    {
        $this->tapIndexColumnCallbacks[] = $callback;

        return $this;
    }

    /**
     * Resolve the index column
     *
     * @return \App\Core\Table\Column|null
     */
    public function resolveIndexColumn()
    {
        $column = is_callable($this->indexColumnCallback) ?
                  call_user_func_array($this->indexColumnCallback, [$this]) :
                  $this->indexColumn();

        if (is_null($column)) {
            return null;
        }

        $column->help($this->helpText);
        $column->hidden(! $this->showOnIndex);

        foreach ($this->tapIndexColumnCallbacks as $callback) {
            tap($column, $callback);
        }

        return $column;
    }
}
