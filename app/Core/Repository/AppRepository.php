<?php

namespace App\Core\Repository;

use Illuminate\Database\Eloquent\Model;

abstract class AppRepository extends BaseRepository
{
    /**
     * Get the model array able relations required when passing data with the response
     *
     * @return array
     */
    public function getResponseRelations()
    {
        if (method_exists($this, 'eagerLoad')) {
            return $this->eagerLoad();
        }

        return [];
    }

    /**
     * Set with property for the query to include the relations required for the response
     *
     * @return static
     */
    public function withResponseRelations()
    {
        return $this->with($this->getResponseRelations());
    }

    /**
     * Run the given callable while being unguarded.
     *
     *
     * @return mixed
     */
    public function unguarded(callable $callback)
    {
        if (Model::isUnguarded()) {
            return $callback($this);
        }

        Model::unguard();

        try {
            return $callback($this);
        } finally {
            Model::reguard();
        }
    }

    /**
     * Set query to order the nulls as last for the given column
     *
     * @param  string  $column
     * @param  string  $direction
     * @return static
     */
    public function orderByNullsLast($column, $direction = 'asc')
    {
        $this->model = $this->model->orderByNullsLast($column, $direction);

        return $this;
    }

    /**
     * Chunk the results of the query.
     *
     * @param  int  $count
     * @return bool
     */
    public function chunk($count, callable $callback)
    {
        $this->applyCriteria();
        $this->applyScope();

        $result = $this->model->chunk($count, $callback);

        $this->resetModel();
        $this->resetScope();

        return $result;
    }

    /**
     * Get a lazy collection for the given query.
     *
     * @return \Illuminate\Support\LazyCollection
     */
    public function cursor()
    {
        $this->applyCriteria();
        $this->applyScope();

        $result = $this->model->cursor();

        $this->resetModel();
        $this->resetScope();

        return $this->parseResult($result);
    }

    /**
     * Query lazily, by chunks of the given size.
     *
     * @param  int  $chunkSize
     * @return \Illuminate\Support\LazyCollection
     *
     * @throws \InvalidArgumentException
     */
    public function lazy($chunkSize = 1000)
    {
        $this->applyCriteria();
        $this->applyScope();

        $result = $this->model->lazy($chunkSize);

        $this->resetModel();
        $this->resetScope();

        return $this->parseResult($result);
    }
}
