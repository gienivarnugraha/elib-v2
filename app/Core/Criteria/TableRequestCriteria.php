<?php

namespace App\Core\Criteria;

use App\Core\Application\Table\Columns\BelongsToColumn;
use App\Core\Application\Table\Columns\HasOneColumn;
use App\Core\Application\Table\Columns\RelationshipColumn;
use App\Core\Application\Table\Table;
use App\Core\Contracts\Countable;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class TableRequestCriteria extends RequestCriteria
{
    /**
     * Initialize new TableRequestCriteria instance.
     *
     * @param  \Illuminate\Support\Collection  $column
     * @param  \App\Core\Table\Table  $table
     */
    public function __construct(protected Collection $columns, protected Table $table)
    {
        parent::__construct($table->request);
    }

    /**
     * Apply order for the current request
     * order=name|asc,created_at|asc,contacts.name|desc
     *
     * @param  mixed  $order
     * @param  \Illumindata\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Builder  $model
     * @return void
     */
    protected function order($orderQuery, $model)
    {
        // No order applied
        if (empty($orderQuery)) {
            return $model;
        }

        // Allowing multiple column to sort like order=name|asc,created_at|asc
        if (stripos($orderQuery, ',')) {
            // @return ['name|asc', 'created_at]
            $orders = explode(',', $orderQuery);
        } else {
            // @return ['name|asc']
            $orders = [$orderQuery];
        }

        $orders = collect($orders);

        // Remove any default order
        if ($orders->isNotEmpty()) {
            $model->reorder();
        }

        $orders->map(function ($order) {
            // Allowing passing sort option like order=created_at|desc
            // @return ['name', 'asc']
            [$field, $direction] = explode('|', $order);

            return [
                'field' => $field,
                'direction' => $direction ?? 'asc',
            ];
        })
            ->reject(fn ($order) => empty($order['field']))
            ->each(function ($order) use (&$model) {
                if (stripos($order['field'], '.')) {
                    [$col, $field] = explode('.', $order['field']);
                } else {
                    $col = $order['field'];
                }

                $column = $this->table->getColumn($col);

                if ($column instanceof RelationshipColumn) {
                    $this->orderByRelationship($column, $order['direction'], $model);
                } else {
                    $model = $model->orderBy($column->attribute, $order['direction']);
                }
            });

        return $model;
    }

    /**
     * Order the query by relationship and check fields
     *
     * @param  \App\Core\Table\Column  $column
     * @param  array  $data
     * @param  \Illumindata\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Builder  $query
     * @return void
     */
    protected function orderByRelationship($column, $direction, $query)
    {
        return match (true) {
            $column instanceof Countable => $query->orderBy($column->attribute, $direction),
            $column instanceof BelongsToColumn => $this->applyOrderWhenBelongsToColumn($column, $direction, $query),
            $column instanceof HasOneColumn => $this->applyOrderWhenHasOneColumn($column, $direction, $query)
        };
    }

    /**
     * Apply order when the column is BelongsTo
     *
     * @param  \App\Core\Table\Column  $column
     * @param  array  $dir
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function applyOrderWhenBelongsToColumn($column, $direction, $query)
    {
        $relationName = $column->relationName;

        // * return contact model
        $model = $query->getModel();

        // * return contact belongs to user (contact.users) => users
        $relation = $model->{$relationName}();

        // * return user_id
        $keyName = $relation->getForeignKeyName();

        // * return users
        $relationTable = $relation->getModel()->getTable();

        // * return contact_users_id_users
        $alias = Str::snake(class_basename($model)).'_'.$relationName.'_'.$relationTable;

        return $query->leftJoin(
            $relationTable.' as '.$alias,
            function ($join) use ($model, $relationName, $keyName, $alias) {
                $join->on($keyName, '=', $alias.'.id');
                $this->mergeExistingAttachedQueries($model, $join, $relationName);
            }
        )->orderBy(
            $alias.'.'.$column->relationField,
            $direction
        );
    }

    /**
     * Apply order when the column is HasOne
     *
     * @param  \App\Core\Table\Column  $column
     * @param  array  $dir
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function applyOrderWhenHasOneColumn($column, $direction, $query)
    {
        $relationName = $column->relationName;

        // * return the model, ex: ContactModel
        $model = $query->getModel();

        // * return the table name of related model, ex: (contacts.user) => users
        $relationTable = $model->{$relationName}()->getModel()->getTable();

        return $query->leftJoin($relationTable, function ($join) use ($relationName, $model) {
            $join->on(
                $model->getQualifiedKeyName(),
                '=',
                $model->{$relationName}()->getQualifiedForeignKeyName()
            );

            $this->mergeExistingAttachedQueries($model, $join, $relationName);
        })->orderBy($column->relationField, $direction);
    }

    /**
     * Merge existing queries in the relation model
     *
     * @param  \Illuminate\Database\Query\Builder  $model The main query builder
     * @param  \Illuminate\Database\Query\Builder  $join
     * @param  string  $relation
     * @return void
     */
    protected function mergeExistingAttachedQueries($model, $join, $relation)
    {
        $builder = $model->{$relation}()
            // Illuminate\Database\Eloquent\Builder
            ->getQuery()
            // Illuminate\Database\Query\Builder
            ->getQuery();

        // Merge existing relation attached queries
        // ["type" => "Null", "column" => "users.id" ,"boolean" => "and"],... etc
        $join->mergeWheres(array_filter($builder->wheres, function ($where) {
            return ! in_array($where['type'], ['Null', 'NotNull']);
        }), $builder->getBindings());
    }
}
