<?php

namespace App\Core\Repository;

use App\Core\Contracts\Repository\Exceptions\RepositoryException;
use App\Core\Contracts\Repository\RepositoryCriteriaInterface;
use App\Core\Contracts\Repository\RepositoryInterface;
use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

abstract class BaseRepository implements RepositoryCriteriaInterface, RepositoryInterface
{
    use HandlesCriteria, HasEvents;

    /**
     * @var \App\Core\Models\Model
     */
    protected $model;

    /**
     * @var \Closure|null
     */
    protected $scopeQuery = null;

    /**
     * Indicates whether the events are booted
     *
     * @var bool
     */
    protected static $eventsBooted = false;

    /**
     * The repositories class names that are booted
     *
     * @var array
     */
    protected static $booted = [];

    /**
     * @var array
     */
    protected static $fieldSearchable = [];

    public function __construct()
    {
        $this->makeModel();
        $this->criteria = new Collection();
        $this->bootEventsIfNotBooted();
        $this->bootIfNotBooted();
    }

    /**
     * Boot the repository if not booted already
     *
     * @return void
     */
    public function bootIfNotBooted()
    {
        if (!isset(static::$booted[static::class])) {
            static::$booted[static::class] = true;

            static::boot();
        }
    }

    /**
     * Boot the events if not booted
     *
     * @return void
     */
    public function bootEventsIfNotBooted()
    {
        if (!static::$eventsBooted) {
            $this->setEventDispatcher($this->app()['events']);
            static::$eventsBooted = true;
        }
    }

    /**
     * Boot the repository
     */
    public static function boot()
    {
        //
    }

    /**
     * @throws \App\Core\Repository\Exceptions\RepositoryException
     */
    public function resetModel()
    {
        $this->makeModel();
    }

    /**
     * Set the repository searchable fields
     *
     *
     * @return static
     */
    public function setSearchableFields(array $fields)
    {
        static::$fieldSearchable = $fields;

        return $this;
    }

    /**
     * Get the repository searchable fields
     */
    public function getFieldsSearchable(): array
    {
        return static::$fieldSearchable;
    }

    /**
     * Get model instance
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function getModel()
    {
        if ($this->model instanceof Builder) {
            return $this->model->getModel();
        }

        return $this->model;
    }

    /**
     * Specify Model class name
     *
     * @return string
     */
    abstract public static function model();

    /**
     * Get the application
     *
     * @return \Illuminate\Container\Container;
     */
    protected function app()
    {
        return app();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Model
     *
     * @throws \App\Core\Repository\Exceptions\RepositoryException
     */
    public function makeModel()
    {
        $model = $this->app()->make($this->model());

        if (!$model instanceof Model) {
            throw new RepositoryException(sprintf(
                'Class %s must be an instance of %s',
                $this->model(),
                Model::class
            ));
        }

        return $this->model = $model;
    }

    /**
     * Provide columns that should be selected
     *
     * @param  array|string  $columns
     * @return static
     */
    public function columns($columns)
    {
        $this->model = $this->model->select($columns);

        return $this;
    }

    /**
     * Count results of repository
     *
     * @param  string  $columns
     * @return int
     */
    public function count(array $where = [], $columns = '*')
    {
        $this->applyCriteria();
        $this->applyScope();

        if ($where) {
            $this->applyConditions($where);
        }

        $result = $this->model->count($columns);

        $this->resetModel();
        $this->resetScope();

        return $result;
    }

    /**
     * Add scoped query
     *
     *
     * @return static
     */
    public function scopeQuery(Closure $scope)
    {
        $this->scopeQuery = $scope;

        return $this;
    }

    /**
     * Retrieve data array for populate field select

     *
     * @param  string  $column
     * @param  string|null  $key
     * @return \Illuminate\Support\Collection|array
     */
    public function pluck($column, $key = null)
    {
        $this->applyCriteria();

        return $this->model->pluck($column, $key);
    }

    /**
     * Retrieve all data of repository
     *
     * @param  array  $columns
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function all($columns = ['*'])
    {
        $this->applyCriteria();
        $this->applyScope();

        if ($this->model instanceof Builder) {
            $results = $this->model->get($columns);
        } else {
            $results = $this->model->all($columns);
        }

        $this->resetModel();
        $this->resetScope();

        return $this->parseResult($results);
    }

    /**
     * Alias of All method
     *
     * @param  array  $columns
     * @return mixed
     */
    public function get($columns = ['*'])
    {
        return $this->all($columns);
    }

    /**
     * Retrieve first data of repository
     *
     * @param  array  $columns
     * @return mixed
     */
    public function first($columns = ['*'])
    {
        $this->applyCriteria();
        $this->applyScope();

        $results = $this->model->first($columns);

        $this->resetModel();

        return $this->parseResult($results);
    }

    /**
     * Retrieve first data of repository, or return new Entity
     *
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function firstOrNew(array $attributes = [], array $values = [])
    {
        $this->makeModel();

        $this->applyCriteria();
        $this->applyScope();

        $model = $this->model->firstOrNew($attributes, $values);

        $this->resetModel();

        return $this->parseResult($model);
    }

    /**
     * Retrieve first data of repository, or create new Entity
     *
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function firstOrCreate(array $attributes = [], array $values = [])
    {
        $this->makeModel();

        $this->applyCriteria();
        $this->applyScope();

        $model = $this->model->firstOrCreate($attributes, $values);

        $this->resetModel();

        return $this->parseResult($model);
    }

    /**
     * Create or update a record matching the attributes, and fill it with values.
     *
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function updateOrCreate(array $attributes = [], array $values = [])
    {
        $this->makeModel();

        $this->applyCriteria();
        $this->applyScope();

        $model = $this->model->updateOrCreate($attributes, $values);

        $this->resetModel();

        return $this->parseResult($model);
    }

    /**
     * Retrieve all data of repository, paginated
     *
     * @param  int|null  $limit
     * @param  array  $columns
     * @return \App\Core\Application\Table\LengthAwarePaginator
     */
    public function paginate($limit = null, $columns = ['*'], $method = 'paginate')
    {
        $this->applyCriteria();
        $this->applyScope();

        $limit = is_null($limit) ? $this->model->getModel()->getPerPage() : $limit;

        $results = $this->model->{$method}($limit, $columns);

        $results->appends(app('request')->query());

        $this->resetModel();

        return $this->parseResult($results);
    }

    /**
     * Set the "limit" value of the query.
     *
     * @param  int  $value
     * @return static
     */
    public function limit($limit)
    {
        $this->model = $this->model->limit($limit);

        return $this;
    }

    /**
     * Retrieve all data of repository, simple paginated
     *
     * @param  int|null  $limit
     * @param  array  $columns
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function simplePaginate($limit = null, $columns = ['*'])
    {
        return $this->paginate($limit, $columns, 'simplePaginate');
    }

    /**
     * Find data by id
     *
     * @param  int  $id
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function find($id)
    {
        $this->applyCriteria();
        $this->applyScope();

        $model = $this->model->findOrFail($id);

        $this->resetModel();

        return $this->parseResult($model);
    }

    /**
     * Find multiple data by their primary keys.
     *
     * @param  array  $ids
     * @param  array  $columns
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function findMany($ids, $columns = ['*'])
    {
        $this->applyCriteria();
        $this->applyScope();

        $model = $this->model->findMany($ids, $columns);

        $this->resetModel();

        return $this->parseResult($model);
    }

    /**
     * Find data by field and value
     *
     * @param  string  $field
     * @param  mixed  $value
     * @param  array  $columns
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function findByField($field, $value = null, $columns = ['*'])
    {
        $this->applyCriteria();
        $this->applyScope();

        $model = $this->model->where($field, '=', $value)->get($columns);

        $this->resetModel();

        return $this->parseResult($model);
    }

    /**
     * Find data by multiple fields
     *
     * @param  array  $columns
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function findWhere(array $where, $columns = ['*'])
    {
        $this->applyCriteria();
        $this->applyScope();

        $this->applyConditions($where);

        $model = $this->model->get($columns);

        $this->resetModel();

        return $this->parseResult($model);
    }

    /**
     * Find data by multiple values in one field
     *
     * @param  string  $field
     * @param  array  $columns
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function findWhereIn($field, array $values, $columns = ['*'])
    {
        $this->applyCriteria();
        $this->applyScope();

        $model = $this->model->whereIn($field, $values)->get($columns);

        $this->resetModel();

        return $this->parseResult($model);
    }

    /**
     * Find data by excluding multiple values in one field
     *
     * @param  string  $field
     * @param  array  $columns
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function findWhereNotIn($field, array $values, $columns = ['*'])
    {
        $this->applyCriteria();
        $this->applyScope();

        $model = $this->model->whereNotIn($field, $values)->get($columns);

        $this->resetModel();

        return $this->parseResult($model);
    }

    /**
     * Find data using whereBetween query
     *
     * @param  string  $column
     * @param  array  $columns
     * @param  string  $boolean
     * @param  bool  $not
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function findWhereBetween($column, array $values, $columns = ['*'], $boolean = 'and', $not = false)
    {
        $this->applyCriteria();
        $this->applyScope();

        $model = $this->model->whereBetween($column, $values, $boolean, $not)->get($columns);

        $this->resetModel();

        return $this->parseResult($model);
    }

    /**
     * Save a new entity in repository
     *
     * @param  array  $attributes
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function create(array $attributes)
    {
        $model = $this->model->newInstance($attributes);

        $this->performInsert($model, $attributes);

        $this->resetModel();

        $result = $this->parseResult($model);

        return $result;
    }

    /**
     * Perform model insert operation
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  array  $attributes
     * @return void
     */
    protected function performInsert($model, $attributes)
    {
        if ($this->fireModelEvent('creating', $model) === false) {
            return false;
        }

        if ($model->save()) {
            $this->fireModelEvent('created', $model, false);
        }
    }

    /**
     * Update a entity in repository by id
     *
     * @param  array  $attributes
     * @param  int  $id
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function update(array $attributes, $id)
    {
        $this->makeModel();
        $this->applyScope();

        $model = $this->model->findOrFail($id);
        $dirty = $this->getDirtyValues($model, $attributes);

        $model->fill($dirty);

        $this->performUpdate($model);
        $this->resetModel();

        $result = $this->parseResult($model);

        return $result;
    }

    /**
     * Check dirty values with its relation
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  array  $attributes
     * @return void
     */
    protected function getDirtyValues($model, $attributes)
    {
        return with([], function ($dirty) use ($model, $attributes) {
            foreach ($attributes as $key => $value) {

                if ($model->isRelation($key)) {
                    $this->updateRelation($model, $key, $value);
                }

                if (Schema::hasColumn($model->getTable(), $key) && $model[$key] !== $value) {
                    $dirty[$key] = $value;
                }
            }

            return $dirty;
        });
    }

    /**
     * update relation
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  mixed  $key
     * @param  mixed  $value
     * @return void
     */
    protected function updateRelation($model, $key, $value)
    {
        $relation = $model->{$key}();

        if ($relation instanceof HasOne || $relation instanceof HasMany || $relation instanceof BelongsTo) {
            $relation->update($value);
        }
    }

    /**
     * Perform model update operation
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  array  $attributes
     * @return void
     */
    protected function performUpdate($model)
    {
        if ($this->fireModelEvent('updating', $model) === false) {
            return false;
        }

        if ($model->save()) {
            $this->fireModelEvent('updated', $model, false);
        }
    }

    /**
     * Delete a entity in repository by id
     *
     * @param  mixed  $id
     * @return bool|array
     */
    public function delete($id)
    {
        $models = $this->createModelsArray($id);
        $onlyOneModel = count($models) === 1;
        $result = !$onlyOneModel ? ['skipped' => [], 'deleted' => []] : false;

        foreach ($models as $model) {
            $deleted = $this->performDeleteOnModelWithEvents($model);

            if ($onlyOneModel) {
                $result = $deleted;
            } elseif ($deleted) {
                $result['deleted'][] = $model->getKey();
            } else {
                $result['skipped'][] = $model->getKey();
            }
        }

        return $result;
    }

    /**
     * Perform delete on the given model
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return bool
     */
    protected function performDeleteOnModel($model)
    {
        return $model->delete();
    }

    /**
     * Perform delete on the given model with events
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return bool
     */
    protected function performDeleteOnModelWithEvents($model)
    {
        if ($this->fireModelEvent('deleting', $model) === false) {
            return false;
        }

        $result = $this->performDeleteOnModel($model);

        $this->fireModelEvent('deleted', $model, false);

        return $result;
    }

    /**
     * Delete multiple entities by given criteria.
     *
     *
     * @return bool
     */
    public function deleteWhere(array $where)
    {
        $this->applyScope();

        $this->applyConditions($where);

        $deleted = $this->model->delete();

        $this->resetModel();

        return $deleted;
    }

    /**
     * Update multiple entities
     *
     *
     * @return bool
     */
    public function massUpdate(array $attributes, array $where = [])
    {
        $this->makeModel();

        $this->applyCriteria();
        $this->applyScope();

        if ($where) {
            $this->applyConditions($where);
        }

        $result = $this->model->update($attributes);

        $this->resetModel();

        return $result;
    }

    /**
     * Order collection by a given column
     *
     * @param  string  $column
     * @param  string  $direction
     * @return static
     */
    public function orderBy($column, $direction = 'asc')
    {
        $this->model = $this->model->orderBy($column, $direction);

        return $this;
    }

    /**
     * Sync relations
     *
     * @param  int  $id
     * @param  string  $relation
     * @param  array  $attributes
     * @param  bool  $detaching
     * @return array
     */
    public function sync($id, $relation, $attributes, $detaching = true)
    {
        return $this->find($id)->{$relation}()->sync($attributes, $detaching);
    }

    /**
     * SyncWithoutDetaching
     *
     * @param  int  $id
     * @param  string  $relation
     * @param  array  $attributes
     * @return array
     */
    public function syncWithoutDetaching($id, $relation, $attributes)
    {
        return $this->sync($id, $relation, $attributes, false);
    }

    /**
     * Attach
     *
     * @param  int  $id
     * @param  string  $relation
     * @param  mixed  $ids
     * @return void
     */
    public function attach($id, $relation, $ids)
    {
        return $this->find($id)->{$relation}()->attach($ids);
    }

    /**
     * Detach
     *
     * @param  int  $id
     * @param  string  $relation
     * @param  mixed  $ids
     * @return int
     */
    public function detach($id, $relation, $ids)
    {
        return $this->find($id)->{$relation}()->detach($ids);
    }

    /**
     * Add a relationship count / exists condition to the query.
     *
     * @param  string|\Illuminate\Database\Eloquent\Relations\Relation  $relation
     * @param  string  $operator
     * @param  int  $count
     * @param  string  $boolean
     * @return \Illuminate\Database\Eloquent\Builder|static
     */
    public function has($relation, $operator = '>=', $count = 1, $boolean = 'and', Closure $callback = null)
    {
        $this->model = $this->model->has($relation, $operator, $count, $boolean, $callback);

        return $this;
    }

    /**
     * Add a relationship count / exists condition to the query.
     *
     * @param  string  $relation
     * @param  string  $boolean
     * @return static
     */
    public function doesntHave($relation, $boolean = 'and', Closure $callback = null)
    {
        $this->model = $this->model->doesntHave($relation, $boolean, $callback);

        return $this;
    }

    /**
     * Load relations
     *
     * @param  array|string  $relations
     * @return static
     */
    public function with($relations)
    {
        $this->model = $this->model->with($relations);

        return $this;
    }

    /**
     * Add subselect queries to count the relations.
     *
     * @param  string|array  $relations
     * @return static
     */
    public function withCount($relations)
    {
        $this->model = $this->model->withCount($relations);

        return $this;
    }

    /**
     * Add subselect queries to include the sum of the relation's column.
     *
     * @param  string|array  $relation
     * @param  string  $column
     * @return static
     */
    public function withSum($relation, $column)
    {
        $this->model = $this->model->withSum($relation, $column);

        return $this;
    }

    /**
     * Add a relationship count / exists condition to the query with where clauses.
     *
     * @param  string  $relation
     * @param  string  $operator
     * @param  int  $count
     * @return static
     */
    public function whereHas($relation, Closure $callback = null, $operator = '>=', $count = 1)
    {
        $this->model = $this->model->whereHas($relation, $callback, $operator, $count);

        return $this;
    }

    /**
     * Add a relationship count / exists condition to the query with where clauses.
     *
     * @param  string  $relation
     * @return static
     */
    public function whereDoesntHave($relation, Closure $callback = null)
    {
        $this->model = $this->model->whereDoesntHave($relation, $callback);

        return $this;
    }

    /**
     * Group results by column
     *
     * @param  string  $by
     * @return static
     */
    public function groupBy($by)
    {
        $this->model = $this->model->groupBy($by);

        return $this;
    }

    /**
     * Get the repository scope query
     *
     * @return \Closure|null
     */
    public function getScope()
    {
        return $this->scopeQuery;
    }

    /**
     * Reset the query scope
     *
     * @return static
     */
    public function resetScope()
    {
        $this->scopeQuery = null;

        return $this;
    }

    /**
     * Apply scope in current Query
     *
     * @return static
     */
    protected function applyScope()
    {
        if (isset($this->scopeQuery) && is_callable($this->scopeQuery)) {
            $callback = $this->scopeQuery;
            $this->model = $callback($this->model);
        }

        return $this;
    }

    /**
     * Applies the given where conditions to the model.
     *
     * @return void
     */
    protected function applyConditions(array $where)
    {
        foreach ($where as $field => $value) {
            if (is_array($value)) {
                [$field, $condition, $val] = $value;
                $this->model = $this->model->where($field, $condition, $val);
            } else {
                $this->model = $this->model->where($field, '=', $value);
            }
        }
    }

    /**
     * Create array of models by the given value
     *
     * @param  mixed  $id
     * @return array
     */
    protected function createModelsArray($value)
    {
        if ($value instanceof Model) {
            $models = [$value];
        } elseif (is_numeric($value) || is_array($value)) {
            $models = new Collection($value);
        } elseif ($value instanceof Collection) {
            $models = $value;
        } else {
            // Eloquent Collection
            $models = $value->all();
        }

        // We will check if the collection has mixes of Id's and models
        if ($models instanceof Collection) {
            $possibleModelIds = $models->filter(function ($model) {
                return is_numeric($model);
            });

            return $models->whereInstanceOf(Model::class)
                ->when($possibleModelIds->isNotEmpty(), function ($collection) use ($possibleModelIds) {
                    return $collection->merge(
                        $this->findMany($possibleModelIds->all())->all()
                    )->all();
                });
        }

        return $models;
    }

    /**
     * Wrapper result data
     *
     * @param  mixed  $result
     * @return mixed
     */
    public function parseResult($result)
    {
        return $result;
    }
}
