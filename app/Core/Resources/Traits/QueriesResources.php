<?php

namespace App\Core\Resources\Traits;

use App\Core\Application\Fields\Relation\HasMany;
use App\Core\Facades\Application;

trait QueriesResources
{
    /**
     * Prepare display query
     *
     * @param  null|\App\Core\Repositories\AppRepository  $repository
     * @return \App\Core\Repositories\AppRepository
     */
    public function displayQuery($repository = null)
    {
        $fields = $this->resolveFields();
        $repository ??= static::repository();

        [$with, $withCount] = static::getEagerloadableRelations($fields);

        $with = $with->merge($repository->getResponseRelations());

        return $repository->withCount($withCount->all())->with($with->all());
    }

    /**
     * Prepare index query
     *
     * @param  null|\App\Core\Repositories\AppRepository  $repository
     * @return \App\Core\Repositories\AppRepository
     */
    public function indexQuery($repository = null)
    {
        $repository ??= static::repository();

        [$with, $withCount] = static::getEagerloadableRelations($this->fieldsForIndexQuery());

        if ($ownCriteria = $this->ownCriteria()) {
            $repository->pushCriteria($ownCriteria);
        }

        return $repository->withCount($withCount->all())->with($with->all());
    }

    /**
     * Get the fields when creating index query
     *
     * @return \Illuminate\Support\Collection
     */
    protected function fieldsForIndexQuery()
    {
        return $this->resolveFields()->reject(function ($field) {
            return $field instanceof HasMany;
        });
    }

    /**
     * Create query when the resource is associated for index
     *
     * @param  \App\Core\Models\Model  $primary
     * @param  bool  $applyOrder
     * @return \App\Core\Repositories\AppRepository
     */
    public function associatedIndexQuery($primary, $applyOrder = true)
    {
        $repository = static::repository();
        $model = $repository->getModel();
        $associateabelRelationName = Application::resourceByModel($primary)->associateableName();

        return tap($repository->columns()
            ->with($this->withWhenAssociated())
            ->whereHas($associateabelRelationName, function ($query) use ($primary) {
                return $query->where($primary->getKeyName(), $primary->getKey());
            }), function ($instance) use ($model, $applyOrder) {
                if ($applyOrder && $model->usesTimestamps()) {
                    $instance->orderBy($model->getQualifiedCreatedAtColumn(), 'desc');
                }
            });
    }

    /**
     * Get the relations to eager load when quering associated records
     */
    public function withWhenAssociated(): array
    {
        return [];
    }

    /**
     * Get the eager loadable relations from the given fields
     */
    public static function getEagerloadableRelations($fields)
    {
        $with = $fields->pluck('belongsToRelation');
        $hasMany = $fields->whereInstanceOf(HasMany::class);
        $morphMany = $fields->whereInstanceOf(MorphMany::class);

        $with = $with->merge($hasMany->filter(function ($field) {
            return $field->count === false;
        })->pluck('hasManyRelationship'))->merge($morphMany->filter(function ($field) {
            return $field->count === false;
        })->pluck('morphManyRelationship'));

        $withCount = $hasMany->push(...$morphMany->all())->filter(function ($field) {
            return $field->count === true;
        })->map(function ($field) {
            $relationName = $field instanceof HasMany ? 'hasManyRelationship' : 'morphManyRelationship';

            return $field->{$relationName}.' as '.$field->countKey();
        });

        return array_map(function ($collection) {
            return $collection->filter()->unique();
        }, [$with, $withCount]);
    }
}
