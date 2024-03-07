<?php

namespace App\Core\Application\Table\Traits;

use App\Core\Application\Table\Columns\BelongsToColumn;
use App\Core\Application\Table\Columns\HasManyColumn;
use App\Core\Application\Table\Columns\HasOneColumn;
use App\Core\Application\Table\Columns\MorphManyColumn;
use App\Core\Application\Table\Columns\MorphToManyColumn;

trait HandlesRelations
{
    /**
     * Get all tables relation to be eager loaded with specific selected fields
     *
     * @return array
     */
    protected function withRelationships()
    {
        $relationSelectedColumns = [];

        return with([], function ($relations) use (&$relationSelectedColumns) {
            $this->getColumns()->reject(function ($column) {
                return !$column->isRelation() ||
                    $column->isHidden() ||
                    ($column->isCountable() && $column->counts());
            })->each(function ($column) use (&$relations, &$relationSelectedColumns) {
                // Check if the relation is already queried
                // E.q. example usage on deals table
                // Column stage name and column stage stage_percentage from the same relation
                // In this case, Laravel will only perform query on the last selected relation
                // and the previous relation will be lost
                // for this reason we need to merge both relation in one select with the
                // selected fields from both relation
                $relationExists = false;
                foreach ($relationSelectedColumns as $existingRelation => $callback) {
                    if ($column->relationName === $existingRelation) {
                        $relationExists = true;
                        break;
                    }
                }

                if (!$relationExists) {
                    $selectRelationFields = $this->getRelationSelectFields($column);

                    $relations[$column->relationName] = function ($query) use ($selectRelationFields) {
                        $query->select($selectRelationFields);
                    };

                    $relationSelectedColumns[$column->relationName] = $selectRelationFields;
                } else {
                    // Merge the selected relation fields
                    $relationSelectedColumns[$column->relationName][] = $this->getSelectableField($column, true);

                    $newSelect = array_unique($relationSelectedColumns[$column->relationName]);

                    // Update the existent relation with the new merged select
                    $relations[$column->relationName] = function ($query) use ($newSelect) {
                        $query->select($newSelect);
                    };
                }
            });

            return $relations;
        });
    }

    /**
     * Get the relations that should be counted
     *
     * @return array
     */
    protected function countedRelationships()
    {
        return $this->getColumns()->reject(function ($column) {
            if (!$column->isCountable() || ($column->isCountable() && !$column->counts())) {
                return true;
            }

            /**
             * Check if the table is sorting by countable has many column
             * If at the moment of the request the column is hidden and the user set
             * e.q. default sorting, an error will be triggered because the relationship
             * count is not queried, in this case
             *
             * In such case, we must query the column when is hidden to perform sorting
             */
            return (bool) ($column->isHidden() && !$this->isSortingByColumn($column));
        })->map(fn ($column) => "{$column->relationName} as {$column->attribute}")
            ->all();
    }

    /**
     * Get fields that should be selected with the eager loaded relation
     * E.q. with(['company:id,name'])
     *
     * @param  \App\Core\Table\Column  $column
     * @return array
     */
    protected function getRelationSelectFields($column)
    {
        $select = [$this->getSelectableField($column, true)];

        // Adds the foreign key name to the select as is needed for Laravel to merge the data from the with query
        if ($column instanceof BelongsToColumn || $column instanceof MorphToManyColumn) {
            $select[] = $this->model->{$column->relationName}()->getRelated()->getQualifiedKeyName();
        } elseif ($column instanceof HasManyColumn || $column instanceof HasOneColumn) {
            $select[] = $this->model->{$column->relationName}()->getQualifiedForeignKeyName();
        } elseif ($column instanceof MorphManyColumn) {
            $select[] = $this->model->{$column->relationName}()->getModel()->getQualifiedKeyName();
            $select[] = $this->model->{$column->relationName}()->getQualifiedForeignKeyName();
        }

        return collect($select)->merge(
            $this->qualifyColumn($column->relationSelectColumns, $column->relationName)
        )->unique()->values()->all();
    }
}
