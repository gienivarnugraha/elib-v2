<?php

namespace App\Core\Application\Table;

use App\Core\Application\Table\Columns\ActionColumn;
use App\Core\Application\Table\Columns\BelongsToColumn;
use App\Core\Application\Table\Columns\DateColumn;
use App\Core\Application\Table\Columns\DateTimeColumn;
use App\Core\Application\Table\Traits\HandlesRelations;
use App\Core\Application\Table\Traits\ParsesResponse;
use App\Core\Contracts\Countable;
use App\Core\Criteria\FilterCriteria;
use App\Core\Criteria\TableRequestCriteria;
use App\Core\Facades\Application;
use App\Core\Repository\BaseRepository;
use App\Core\Traits\ProvidesModelAuthorizations;
use App\Core\Traits\ResolvesFilters;
use Illuminate\Http\Request;
use Illuminate\Support\before;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class Table
{
    use HandlesRelations,
        ParsesResponse,
        // ResolvesActions,
        ProvidesModelAuthorizations,
        ResolvesFilters;

    /**
     * Additional relations to eager load on every query.
     */
    protected array $with = [];

    /**
     * Additional countable relations to eager load on every query.
     */
    protected array $withCount = [];

    /**
     * Whether the table has actions column
     */
    public bool $withActionsColumn = false;

    /**
     * Table identifier
     */
    protected string $identifier;

    /**
     * Additional request query string for the table request
     */
    public array $requestQueryString = [];

    /**
     * Table order
     */
    public array $order = [];

    /**
     * Table default per page value
     */
    public int $perPage = 25;

    /**
     * Table max height
     *
     * @var int|null
     */
    public $maxHeight = null;

    /**
     * The repository original model
     *
     * @var \Illuminate\Database\Eloquent\Model
     */
    protected $model = null;

    /**
     * The column collection
     */
    protected Collection $columns;

    /**
     * The table settings
     */
    protected TableSettings $settings;

    /**
     * Initialize new Table instance.
     */
    public function __construct(protected BaseRepository $repository, public Request $request)
    {
        $this->model = $this->repository->getModel();
        $this->identifier = Str::kebab(class_basename(static::class));
        $this->setColumns($this->columns());

        $this->boot();
        $this->settings = new TableSettings($this); // $request->user()
    }

    /**
     * Set table identifier
     */
    public function setIdentifier(string $key): static
    {
        $this->identifier = $key;

        return $this;
    }

    /**
     * Set table identifier
     */
    public function identifier()
    {
        return $this->identifier;
    }

    /**
     * Custom boot method
     */
    public function boot(): void
    {
        //
    }

    /**
     * Creates the table data and return the data
     */
    public function make(): LengthAwarePaginator
    {
        // We will count the all time total before any filters and criterias
        $scope = $this->repository->getScope();
        $totalitems = $this->repository->resetScope()->skipCriteria()->count();
        $this->repository->skipCriteria(false);

        if ($scope) {
            $this->repository->scopeQuery($scope);
        }

        $this->repository->pushCriteria(new TableRequestCriteria($this->getColumns(), $this));

        $this->setSearchableFields();

        $this->repository->pushCriteria(new FilterCriteria($this->request, $this->resolveFilters()));

        // If you're combining withCount with a select statement,
        // ensure that you call withCount after the select method
        $response = $this->repository
            ->columns($this->getSelectColumns())
            ->with(array_merge($this->withRelationships(), $this->with))
            ->withCount(array_merge($this->countedRelationships(), $this->withCount))
            ->paginate((int) $this->request->get('per_page', $this->perPage));

        return $this->parseResponse($response, $totalitems);
    }

    /**
     * Get additional select columns for the query
     */
    protected function addSelect(): array
    {
        return [];
    }

    /**
     * Append model attributes
     */
    protected function appends(): array
    {
        return [];
    }

    /**
     * Provides table columns
     */
    public function columns(): array
    {
        return [];
    }

    /**
     * Set the table column
     */
    public function setColumns(array $columns): static
    {
        $this->columns = new Collection($columns);

        if ($this->withActionsColumn === true) {
            // Check if we need to add the action
            if (!$this->columns->whereInstanceOf(ActionColumn::class)->first()) {
                $this->addColumn(new ActionColumn);
            }
        }

        return $this;
    }

    /**
     * Add new column to the table
     *
     * @param  \App\Core\Table\Column  $column
     */
    public function addColumn(Column $column): static
    {
        $this->columns->push($column);

        return $this;
    }

    /**
     * Set table default order by
     *
     * @param  string  $dir asc|desc
     */
    public function orderBy(string $attribute, string $dir = 'asc'): static
    {
        $this->order[] = ['attribute' => $attribute, 'direction' => $dir];

        return $this;
    }

    /**
     * Clear the order by attributes
     */
    public function clearOrderBy(): static
    {
        $this->order = [];

        return $this;
    }

    /**
     * Add additional relations to eager load
     */
    public function with(string|array $relations): static
    {
        $this->with = array_merge($this->with, (array) $relations);

        return $this;
    }

    /**
     * Add additional countable relations to eager load
     */
    public function withCount(string|array $relations): static
    {
        $this->withCount = array_merge($this->withCount, (array) $relations);

        return $this;
    }

    /**
     * Get defined column by given attribute
     *
     *
     * @return \App\Core\Table\Column|null
     */
    public function getColumn(string $attribute): ?Column
    {
        return $this->columns->firstWhere('attribute', $attribute);
    }

    /**
     * Get table available columns
     *
     * @see \App\Core\Table\CustomizesTable
     */
    public function getColumns(): Collection
    {
        return $this->columns->filter->authorizedToSee()->values();
    }

    /**
     * Check if the table is sorted by specific column
     *
     * @param  \App\Core\Table\Column  $column
     */
    public function isSortingByColumn(Column $column): bool
    {
        $sortingBy = $this->request->get('order');

        $sortedByFields = data_get($sortingBy, '*.attribute');

        return in_array($column->attribute, $sortedByFields);
    }

    /**
     * Get select columns
     *
     * Will return that columns only that are needed for the table
     * For example of the user made some columns not visible they won't be queried
     */
    protected function getSelectColumns(): array
    {
        $columns = $this->getColumns();
        $select = [];

        foreach ($columns as $column) {
            if ($column->isHidden() && !$column->queryWhenHidden) {
                continue;
            }

            if (!$column->isRelation()) {
                if ($field = $this->getSelectableField($column)) {
                    $select[] = $field;
                }
            } elseif ($column instanceof BelongsToColumn) {
                // Select the foreign key name for the BelongsToColumn
                // If not selected, the relation won't be queried properly
                $select[] = $this->model->{$column->relationName}()->getQualifiedForeignKeyName();
            }
        }

        return array_unique(array_merge(
            $this->qualifyColumn($this->addSelect()),
            [$this->model->getQualifiedKeyName() . ' as ' . $this->model->getKeyName()],
            $select
        ));
    }

    /**
     * Set the repository searchable fields based on the
     * visible columns
     */
    protected function setSearchableFields(): void
    {
        $fields = $this->getSearchableColumns()->filter(function ($column) {
            if ($column->isRelation()) {
                // We will check if the column is exist in the repository $searchable_fields
                return in_array($column->relationField, $this->getRelationSearchableFields($column));
            } else {
                return true;
            }
        })->mapWithKeys(function ($column) {
            // set searchable field attribute based on the relation
            // if the column is not a relation get the original attribute
            if ($column->isRelation()) {
                $searchableField = $column->relationName . '.' . $column->relationField;
            } else {
                $searchableField = $column->attribute;
            }

            return [$searchableField => 'like'];
        })->all();

        $this->repository->setSearchableFields($fields);
    }

    /**
     * find the searchable fields for the given resource
     *
     * @return array of searchable fields
     */
    protected function getRelationSearchableFields($column): array
    {

        if ($column instanceof BelongsToColumn) {
            $relationName = class_basename($this->model->{$column->relationName}()->getRelated());
            $name = Str::plural(Str::lower($relationName));
        } else {
            $name = $column->relationName;
        }

        throw_if(Application::resourceByName($name) === null, 'The resource ' . $name . ' does not exist');

        return array_keys(Application::resourceByName($name)->repository()->getFieldsSearchable());
    }

    /**
     * Filter the searchable columns
     */
    protected function getSearchableColumns(): Collection
    {
        return $this->getColumns()->filter(function ($column) {
            // We will check if the column is date column, as date columns are not searchable
            // as there won't be accurate results because the database dates are stored in UTC timezone
            // In this case, the filters must be used
            // Additionally we will check if is countable column and the column counts
            if (
                $column instanceof DateTimeColumn ||
                $column instanceof DateColumn ||
                $column instanceof Countable && $column->counts()
            ) {
                return false;
            }

            // Relation columns with no custom query are searchable
            if ($column->isRelation()) {
                return empty($column->queryAs);
            }

            // Regular database, and also is not queried
            // with DB::raw, when querying with DB::raw, you must implement
            // custom searching criteria
            return empty($column->queryAs);
        });
    }

    /**
     * Get the server for the table AJAX request params
     */
    public function getRequestQueryString(): array
    {
        return $this->requestQueryString;
    }

    /**
     * Get the table settings for the given request
     */
    public function settings(): TableSettings
    {
        return $this->settings;
    }

    /**
     * Get field by column that should be included in the table select query
     *
     * @see  For $isRelationWith take a look in \App\Core\Table\HandlesRelations
     *
     * @param  \App\Core\Table\Column  $column
     * @param  bool  $isRelationWith Whether this field will be used for eager loading
     * @return mixed
     */
    protected function getSelectableField(Column $column, $isRelationWith = false)
    {
        if ($column instanceof ActionColumn) {
            return null;
        }

        if (!empty($column->queryAs)) {
            return $column->queryAs;
        } elseif ($isRelationWith) {
            return $this->qualifyColumn($column->relationField, $column->relationName);
        }

        return $this->qualifyColumn($column->attribute);
    }

    /**
     * Qualify the given column
     *
     * @param  string[]  $column
     * @param  string|null  $relationName
     * @return mixed
     */
    protected function qualifyColumn($column, $relationName = null)
    {
        if (is_array($column)) {
            return array_map(fn ($column) => $this->qualifyColumn($column, $relationName), $column);
        }

        if ($relationName) {
            return $this->model->{$relationName}()->qualifyColumn($column);
        }

        return $this->model->qualifyColumn($column);
    }
}
