<?php

namespace App\Core\Application\Fields\Relation;

use App\Core\Application\Table\Columns\MorphToManyColumn;

class MorphToMany extends HasMany
{
    /**
     * Provide the column used for index
     *
     * @return \App\Core\Table\MorphToManyColumn
     */
    public function indexColumn(): MorphToManyColumn
    {
        return tap(new MorphToManyColumn(
            $this->hasManyRelationship,
            $this->labelKey,
            $this->label
        ), function ($column) {
            if ($this->counts()) {
                $column->count()->centered()->sortable();
            }
        });
    }
}
