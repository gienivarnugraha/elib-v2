<?php

namespace App\Core\Criteria;

use App\Core\Contracts\Repository\CriteriaInterface;
use App\Core\Contracts\Repository\RepositoryInterface;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class RequestCriteria implements CriteriaInterface
{
    /**
     * Append additional criterias within the request criteria
     */
    protected array $appends = [];

    /**
     * Initialize new RequestCriteria class
     */
    public function __construct(protected Request $request)
    {
    }

    /**
     * Apply criteria in query repository
     *
     * @param  \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Builder  $model
     * @return mixed
     */
    public function apply($model, RepositoryInterface $repository)
    {
        $fieldsSearchable = $repository->getFieldsSearchable();

        // search query
        // q=contacts.name:Contact Name
        // q=first_name:John;source.name:Source
        $searchQuery = $this->request->get('q', null);

        // fields to perform search
        // ex: search_fields=first_name:like;last_name:=
        $searchFields = $this->request->get('search_fields', null);

        // search match to perform query
        // ex:search_match=or|and
        $searchMatch = $this->request->get('search_match', null);

        // column to select with
        // ex: select=id;first_name;last_name
        // or array 'select' => ['id', 'first_name', 'email']
        $select = $this->request->get('select', null);

        // order with
        // ex:select=contacts|asc,contacts.deals|asc
        // or array 'order' => ['field' => 'contacts:contact_id|created_at', 'direction' => 'desc'
        $order = $this->request->get('order', null);

        // model to eager load with search ex:contacts;deals
        // multiple ex: with=source;user'
        // single ex: with=source'
        // array ex: 'with' => ['source', 'user']
        $with = $this->request->get('with', null);

        // how many result to take, ex: take=2
        $take = $this->request->get('take', null);

        // if search query exist and attribute are searchable
        if ($searchQuery && is_array($fieldsSearchable) && count($fieldsSearchable)) {
            $isFirstField = true;

            // Parse the search fields based on the provided searchable fields and search string
            $fields = $this->parseSearchFields($fieldsSearchable, $searchFields);

            // Parse the search data based on the provided search query
            $searchData = $this->parserSearchData($searchQuery);

            // Parse the search value from the search query
            $searchQuery = $this->parseSearchValue($searchQuery);

            // Determine whether to use the "AND" or "OR" operator for the search conditions
            $modelForceAndWhere = strtolower($searchMatch) === 'and';

            // If the search query is for searching by ID, update the fields to only include the primary key field
            if ($this->shouldSearchOnlyById($searchQuery)) {
                $fields = [$model->getModel()->getKeyName()];
            }

            // Apply the search conditions to the model query
            $model = $model->where(function ($query) use (
                $fields,
                $searchQuery,
                $searchData,
                $isFirstField,
                $modelForceAndWhere,
                $repository
            ) {
                /** @var Builder $query */

                // Iterate over each field and condition to build the search conditions
                foreach ($fields as $field => $condition) {
                    if (is_numeric($field)) {
                        // If the field is numeric, assume it's the field name and the condition is "="
                        $field = $condition;
                        $condition = '=';
                    }

                    $value = null;

                    $condition = trim(strtolower($condition));

                    // Determine the value to search for based on the search data or search query
                    if (isset($searchData[$field])) {
                        $value = ($condition == 'like' || $condition == 'ilike') ?
                            "%{$searchData[$field]}%" :
                            $searchData[$field];
                    } else {
                        if (! is_null($searchQuery)) {
                            $value = ($condition == 'like' || $condition == 'ilike') ?
                                "%{$searchQuery}%" :
                                $searchQuery;
                        }
                    }

                    $relation = null;

                    // If the field contains a dot, assume it's a relationship field and extract the relation and field names
                    if (stripos($field, '.')) {
                        [$relation, $field] = explode('.', $field);
                    }

                    // Apply the search condition to the query
                    if (! is_null($value)) {
                        $whereCondition = 'and';

                        if ($isFirstField || $modelForceAndWhere) {
                            $isFirstField = false;
                        } else {
                            // Apply the search condition using the "OR" operator
                            $whereCondition = 'or';
                        }

                        // Apply the search condition using the "AND" operator
                        $this->applySearch($value, $query, $field, $relation, $condition, $whereCondition);
                    }
                }

                // Apply any additional search criteria
                foreach ($this->appends as $criteria) {
                    $query = $criteria->apply($query, $repository);
                }
            });
        }

        if ($take) {
            $model = $model->take($take);
        }

        $model = $this->order($order, $model);
        $model = $this->select($select, $model);
        $model = $this->with($with, $model);

        return $model;
    }

    /**
     * Apply and where search
     *
     * @param  string  $value
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $field
     * @param  string|null  $relation
     * @param  string  $condition
     * @param  string  $whereCondition whereHas|orWhereHas
     * @return void
     */
    protected function applySearch($value, $query, $field, $relation, $condition, $whereCondition)
    {
        $whereQuery = $whereCondition === 'and' ? 'whereHas' : 'orWhereHas';
        $whereQualify = $whereCondition === 'and' ? 'where' : 'orWhere';

        if (! is_null($relation)) {
            $query->{$whereQuery}($relation, function ($query) use ($field, $condition, $value) {
                $query->where($field, $condition, $value);
            });

            return;
        }

        $query->{$whereQualify}($query->qualifyColumn($field), $condition, $value);
    }

    /**
     * Append additional criteria within the request query
     *
     * @return static
     */
    public function appends(CriteriaInterface $criteria)
    {
        $this->appends[] = $criteria;

        return $this;
    }

    /**
     * Apply order for the current request
     *
     * @param  mixed  $order
     * @param  \Illuminate\Database\Eloquent\Builder  $model
     * @return void
     */
    protected function order($order, $model)
    {
        // No order applied
        if (empty($order)) {
            return $model;
        }

        // Allowing passing sort option like order=created_at|desc
        if (! is_array($order)) {
            [$field, $direction] = explode('|', $order);

            $order = [
                'field' => $field,
                'direction' => $direction ?? '',
            ];
        }

        // Is not multidimensional array, order by one field and direction
        // e.q. ['field'=>'fieldName', 'direction'=>'asc']
        if (isset($order['field'])) {
            $order = [$order];
        }

        $order = collect($order)->reject(fn ($order) => empty($order['field']));

        // Remove any default order
        // Check if the order is not empty
        if ($order->isNotEmpty()) {
            // Reorder the model
            $model = $model->reorder();
        }

        // Map over each order
        $order->map(
            // Use arrow function to merge order array with default direction
            fn ($order) => array_merge($order, [
                'direction' => ($order['direction'] ?? '') ?: 'asc',
            ])
        )->each(function ($order) use (&$model) {
            // Destructure the field and direction from the order array
            ['field' => $field, 'direction' => $direction] = $order;
            // Split the field by '|'
            $split = explode('|', $field);

            // Check if the field has multiple parts
            if (count($split) > 1) {
                // Order by relationship
                $this->orderByRelationship($split, $direction, $model);
            } else {
                // Order by field and direction
                $model = $model->orderBy($field, $direction);
            }
        });

        return $model;
    }

    /**
     * Order the query by relationship
     *
     * @param  array  $orderData
     * @param  string  $dir
     * @param  \Illuminate\Database\Eloquent\Builder  $model
     * @return void
     */
    protected function orderByRelationship($orderData, $dir, $model)
    {
        /*
        * ex.
        * products|description -> join products on current_table.product_id = products.id order by description
        *
        * products:custom_id|products.description -> join products on current_table.custom_id = products.id order
        * by products.description (in case both tables have same column name)
        */
        $table = $model->getModel()->getTable();
        $sortTable = $orderData[0];
        $sortColumn = $orderData[1];

        $orderData = explode(':', $sortTable);

        if (count($orderData) > 1) {
            $sortTable = $orderData[0];
            $keyName = $table.'.'.$orderData[1];
        } else {
            /*
             * If you do not define which column to use as a joining column on current table, it will
             * use a singular of a join table appended with _id
             *
             * ex.
             * products -> product_id
             */
            $prefix = Str::singular($sortTable);
            $keyName = $table.'.'.$prefix.'_id';
        }

        $model = $model
            ->leftJoin($sortTable, $keyName, '=', $sortTable.'.id')
            ->orderBy($sortTable.'.'.$sortColumn, $dir)
            ->addSelect($table.'.*');
    }

    /**
     * Apply select fields to model
     *
     * @param  mixed  $select
     * @param  \Illuminate\Database\Eloquent\Builder  $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function select($select, $model)
    {
        if (! empty($select)) {
            if (is_string($select)) {
                $select = explode(';', $select);
            }

            $model = $model->select($select);
        }

        return $model;
    }

    /**
     * Apply with relationships to model
     *
     * @param  mixed  $with
     * @param  \Illuminate\Database\Eloquent\Builder  $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function with($with, $model)
    {
        if ($with) {
            if (is_string($with)) {
                $with = explode(';', $with);
            }

            $model = $model->with($with);
        }

        return $model;
    }

    /**
     * @param  string  $query
     * @return array
     */
    protected function parserSearchData($query)
    {
        $searchData = [];

        if (stripos($query, ':')) {
            $fields = explode(';', $query);

            foreach ($fields as $row) {
                try {
                    [$field, $value] = explode(':', $row);
                    $searchData[$field] = $value;
                } catch (Exception $e) {
                    //Surround offset error
                }
            }
        }

        return $searchData;
    }

    /**
     * @param  string  $query
     * @return null
     */
    protected function parseSearchValue($query)
    {
        if (stripos($query, ';') || stripos($query, ':')) {
            $values = explode(';', $query);

            foreach ($values as $value) {
                $s = explode(':', $value);

                if (count($s) == 1) {
                    return $s[0];
                }
            }

            return;
        }

        return $query;
    }

    /**
     * Parse the searchable fields
     * ex: search_fields=first_name:like;last_name:like&search_match=or|and
     *
     * @param  array  $fields
     * @param  array|null  $searchFields
     * @return array $whitelisted
     */
    protected function parseSearchFields($searchableFields = [], $searchFields = null)
    {

        if (is_null($searchFields) || count($searchFields) === 0) {
            return $searchableFields;
        }

        $searchFields = is_array($searchFields) ? $searchFields : explode(';', $searchFields);

        $acceptedConditions = ['=', 'like'];

        $whitelisted = [];

        foreach ($searchFields as $index => $field) {
            $parts = explode(':', $field);
            $field = $parts[0];
            $condition = $parts[1];
            $temporaryIndex = array_search($field, $searchableFields);

            if (count($parts) == 2 && in_array($condition, $acceptedConditions)) {
                unset($searchableFields[$temporaryIndex]);
                $searchableFields[$field] = $condition;
                $searchFields[$index] = $field;
            }
        }

        foreach ($searchableFields as $field => $condition) {
            if (is_numeric($field)) {
                $field = $condition;
                $condition = '=';
            }

            if (in_array($field, $searchFields)) {
                $whitelisted[$field] = $condition;
            }
        }

        abort_unless(
            count($whitelisted),
            403,
            sprintf(
                'None of the search fields were accepted. Acceptable search fields are: %s',
                implode(',', array_keys($searchableFields))
            )
        );

        return $whitelisted;
    }

    /**
     * Check whether the search should be performed only by id
     * This works even when the ID is not allowed as searchable field.
     *
     * @param  mixed  $value
     * @return bool
     */
    protected function shouldSearchOnlyById($value)
    {
        if (
            is_array($value) ||

            // Is not integer and not all string characters are digits
            (! is_int($value) && ! ctype_digit($value)) ||

            // String and starts with 0, probably not ID for example phone numberc etc...
            (is_string($value) && substr($value, 0, 1) === '0') ||

            // If value less then 1, probably not ID value
            // As well if the value length is bigger then 20, as BigIncrement column length is 20
            ((int) $value < 1 || strlen((string) $value) > 20)
        ) {
            return false;
        }
    }
}
