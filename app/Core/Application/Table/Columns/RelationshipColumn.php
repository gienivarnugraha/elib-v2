<?php

namespace App\Core\Application\Table\Columns;

use App\Core\Application\Table\Column;
use Illuminate\Support\Str;

class RelationshipColumn extends Column
{
    /**
     * Attributes to append with the response
     *
     * @var array
     */
    public $appends = [];

    /**
     * The relation name
     *
     * @var string
     */
    public $relationName;

    /**
     * The relation field
     *
     * @var string
     */
    public $relationField;

    /**
     * Additional fields to select
     *
     * @see @method select
     *
     * @var array
     */
    public $relationSelectColumns = [];

    /**
     * Initialize new RelationshipColumn instance.
     *
     * @param  string  $name
     * @param  string  $field
     */
    public function __construct($name, ?string $attribute, string $label = null)
    {
        // The relation names for front-end are returned in snake case format.
        parent::__construct(Str::snake($name), $label);

        $this->relationName = $name;
        $this->relationField = $attribute;
    }

    /**
     * Additional select for a relation
     *
     * For relation e.q. MorphToManyColumn::make('contacts', 'first_name', 'Contacts')->select(['avatar', 'email'])
     */
    public function select(array|string $fields): static
    {
        $this->relationSelectColumns = array_merge(
            $this->relationSelectColumns,
            (array) $fields
        );

        return $this;
    }

    /**
     * Set attributes to appends in the model
     */
    public function appends(array|string $attributes): static
    {
        $this->appends = (array) $attributes;

        return $this;
    }

    /**
     * Check whether the column is a relation
     */
    public function getQualifiedName(): string
    {
        if ($this->isRelation()) {
            return $this->relationName . '.' . $this->relationField;
        } else {
            return null;
        }
    }

    /**
     * toArray
     *
     * @return array
     */
    public function toArray()
    {
        return array_merge(parent::toArray(), [
            'relationField' => $this->relationField,
        ]);
    }
}
