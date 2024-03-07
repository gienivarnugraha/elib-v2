<?php

namespace App\Core\Application\Cards;


use App\Core\Http\Resources\JsonResource;
use Illuminate\Support\Facades\Request;
use App\Core\Contracts\Presentable;
use Illuminate\Database\Query\Expression;
use App\Core\Criteria\RequestCriteria;
use App\Core\Traits\ProvidesModelAuthorizations;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

abstract class TableAsyncCard extends Card
{
    use ProvidesModelAuthorizations;

    /**
     * Default sort field
     *
     * @var \Illuminate\Database\Query\Expression|string|null
     */
    protected Expression|string|null $sortBy = 'id';

    /**
     * Default sort direction
     *
     * @var string
     */
    protected string $sortDirection = 'asc';

    /**
     * Default per page
     *
     * @var integer
     */
    protected int $perPage = 15;

    /**
     * @var \App\Core\Repository\BaseRepository
     */
    protected $repository;

    /**
     * Provide the repository that will be used to retrieve the items
     *
     * @return \App\Core\Repository\BaseRepository
     */
    abstract public function repository();

    /**
     * Get the repository
     *
     * @return \App\Core\Repository\BaseRepository
     */
    protected function getRepository()
    {
        if (!$this->repository) {
            $this->repository = $this->repository()->pushCriteria(new RequestCriteria(
                Request::instance()
            ));
        }

        return $this->repository;
    }

    /**
     * Query the repository for records
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    protected function query(): LengthAwarePaginator
    {
        $repository = $this->getRepository();

        if ($sortBy = $this->getSortColumn()) {
            $repository->orderBy($sortBy, $this->sortDirection);
        }

        return tap($repository->paginate(
            $this->getPerPage(),
            $this->selectColumns()
        ), function ($data) use ($repository) {
            $repository->resetCriteria();
            $this->repository = null;
        });
    }

    /**
     * Get the sort column
     *
     * @return \Illuminate\Database\Query\Expression|string|null
     */
    protected function getSortColumn(): Expression|string|null
    {
        return $this->sortBy;
    }

    /**
     * Get the number of models to return per page.
     *
     * @return int
     */
    protected function getPerPage(): int
    {
        return (int) Request::input('per_page', $this->perPage);
    }

    /**
     * Provide the table fields
     *
     * @return array
     */
    public function fields(): array
    {
        return [];
    }

    /**
     * Get the columns that should be selected in the query
     *
     * @return array
     */
    protected function selectColumns(): array
    {
        return collect($this->fields())->reject(function ($field) {
            return isset($field['select']) && $field['select'] === false;
        })->pluck('key')->push(
            $this->getRepository()->getModel()->getKeyName()
        )->all();
    }

    /**
     * Parse the query result
     *
     * @param \Illuminate\Contracts\Pagination\LengthAwarePaginator $result
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    protected function transformResult(LengthAwarePaginator $result): LengthAwarePaginator
    {
        $result->getCollection()->transform(fn ($model) => $this->mapRow($model));

        return $result;
    }

    /**
     * Map the given model into a row
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     *
     * @return array
     */
    protected function mapRow($model)
    {
        $result = collect($this->fields())
            ->merge(array_map(fn ($column) => ['key' => $column], $this->selectColumns()))
            ->unique('key')
            ->mapWithKeys(function ($field) use ($model) {
                $value = isset($field['format']) ? $field['format']($model) : data_get($model, $field['key']);

                return [$field['key'] => $value];
            })->all();

        if ($model instanceof Presentable) {
            $result['path'] = $model->path;
        }

        $result['authorizations'] = $this->getAuthorizations($model);

        return $result;
    }

    /**
     * Define the card component used on front end
     *
     * @return string
     */
    public function component(): string
    {
        return 'card-with-async-table';
    }

    /**
     * jsonSerialize
     *
     * @return array
     */
    public function jsonSerialize(): array
    {
        return array_merge(parent::jsonSerialize(), [
            'fields' => $this->fields(),
            'items'  => JsonResource::collection($this->transformResult($this->query()))
                ->toResponse(Request::instance())
                ->getData(),
        ]);
    }
}
