<?php

namespace App\Core\Application\Table;

use Closure;
use JsonSerializable;
use Illuminate\Support\Str;
use App\Core\Traits\Metable;
use App\Core\Traits\Makeable;
use App\Core\Contracts\Countable;
use App\Core\Traits\Authorizeable;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Database\Query\Expression;
use App\Core\Application\Table\Columns\RelationshipColumn;

class Column implements Arrayable, JsonSerializable
{
    use Authorizeable,
        Makeable,
        Metable;

    /**
     * Custom query for this field
     *
     * @var mixed
     */
    public $queryAs;

    /**
     * Indicates whether the column is sortable
     */
    public bool $sortable = true;

    /**
     * The column component
     */
    public string $component = 'v-column';

    /**
     * Indicates whether the column is hidden
     */
    public ?bool $hidden = null;

    /**
     * Indicates special columns and some actions are disabled
     */
    public bool $primary = false;

    /**
     * Table th/td min width
     */
    public string $minWidth = '200px';

    /**
     * The column default order
     *
     * @var null
     */
    public ?int $order = null;

    /**
     * Indicates whether to include the column in the query when it's hidden
     */
    public bool $queryWhenHidden = false;

    /**
     * Custom column display formatter
     */
    public ?Closure $displayAs = null;

    /**
     * Column help text
     */
    public ?string $helpText = null;

    /**
     * Data heading component
     *
     * @var string
     */

    /**
     * Initialize new Column instance.
     */
    public function __construct(public ?string $attribute = null, public ?string $label = null)
    {
    }

    /**
     * Set column name/label
     */
    public function label(?string $label): static
    {
        $this->label = $label;

        return $this;
    }

    /**
     * get field label
     *
     * @param  string  $label
     */
    public function getLabel(): string
    {
        return $this->label ?? Str::of($this->attribute)->snake()->replace('_', ' ')->title();
    }

    /**
     * Set column name/label
     */
    public function component(?string $component): static
    {
        $this->component = $component;

        return $this;
    }

    /**
     * Set whether the column is sortable
     */
    public function sortable(bool $bool = true): static
    {
        $this->sortable = $bool;

        return $this;
    }

    /**
     * Check whether the column is primary
     */
    public function primary(bool $bool = false)
    {
        $this->primary = $bool;

        return $this;
    }


    /**
     * Set the column help text
     */
    public function help(?string $text): static
    {
        $this->helpText = $text;

        return $this;
    }

    /**
     * Check whether the column is sortable
     */
    public function isSortable(): bool
    {
        return $this->sortable === true;
    }

    /**
     * Set column visibility
     */
    public function hidden(bool $bool = true): static
    {
        $this->hidden = $bool;

        return $this;
    }

    /**
     * Check whether the column is hidden
     */
    public function isHidden(): bool
    {
        return $this->hidden === true;
    }

    /**
     * Set the column default order
     */
    public function order(int $order): static
    {
        $this->order = $order;

        return $this;
    }

    /**
     * Whether to select/query the column when the column hidden
     */
    public function queryWhenHidden(bool $bool = true): static
    {
        $this->queryWhenHidden = $bool;

        return $this;
    }

    /**
     * Custom query for this column
     *
     * @param \Illuminate\Database\Query\Expression|string|\Closure $queryAs
     *
     * @return static
     */
    public function queryAs(Expression|Closure|string $queryAs): static
    {
        $this->queryAs = $queryAs;

        return $this;
    }


    /**
     * Custom column formatter
     */
    public function displayAs(Closure $callback): static
    {
        $this->displayAs = $callback;

        return $this;
    }

    /**
     * Check whether a column can count relations
     */
    public function isCountable(): bool
    {
        return $this instanceof Countable;
    }

    /**
     * Check whether the column is a relation
     */
    public function isRelation(): bool
    {
        return $this instanceof RelationshipColumn;
    }

    /**
     * toArray
     *
     * @return array
     */
    public function toArray()
    {
        return array_merge([
            'attribute' => $this->attribute,
            'label' => $this->getLabel(),
            'sortable' => $this->isSortable(),
            'hidden' => $this->isHidden(),
            'minWidth' => $this->minWidth,
            'order' => $this->order,
            'primary' => $this->primary,
            'helpText' => $this->helpText,
            'component' => $this->component,
            'isCountable' => $this->isCountable(),
        ], $this->meta());
    }

    /**
     * jsonSerialize
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
