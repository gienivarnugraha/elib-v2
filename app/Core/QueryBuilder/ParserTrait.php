<?php

namespace App\Core\QueryBuilder;

use App\Core\Application\Date\Carbon;
use App\Core\Application\Filters\Fields\Date;
use App\Core\Application\Filters\Fields\DateTime;
use App\Core\QueryBuilder\Exceptions\QueryBuilderException;
use App\Core\Traits\ProvidesBetweenDateArgumentsViaString;
use Closure;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Expression;
use Illuminate\Support\Str;
use stdClass;

trait ParserTrait
{
    use ProvidesBetweenDateArgumentsViaString;

    /**
     * Available operators
     *
     * @var array
     */
    protected $operators = [
        'is' => ['accept_values' => true, 'apply_to' => ['date']],
        'was' => ['accept_values' => true, 'apply_to' => ['date']],
        'equal' => ['accept_values' => true, 'apply_to' => ['text', 'number', 'numeric', 'date', 'radio', 'select']],
        'not_equal' => ['accept_values' => true, 'apply_to' => ['text', 'number', 'numeric', 'date', 'select']],
        'in' => ['accept_values' => true, 'apply_to' => ['multi-select', 'checkbox']],
        'not_in' => ['accept_values' => true, 'apply_to' => ['multi-select']],
        'less' => ['accept_values' => true, 'apply_to' => ['number', 'numeric', 'date']],
        'less_or_equal' => ['accept_values' => true, 'apply_to' => ['number', 'numeric', 'date']],
        'greater' => ['accept_values' => true, 'apply_to' => ['number', 'numeric', 'date']],
        'greater_or_equal' => ['accept_values' => true, 'apply_to' => ['number', 'numeric', 'date']],
        'between' => ['accept_values' => true, 'apply_to' => ['number', 'numeric', 'date']],
        'not_between' => ['accept_values' => true, 'apply_to' => ['number', 'numeric', 'date']],
        'begins_with' => ['accept_values' => true, 'apply_to' => ['text']],
        'not_begins_with' => ['accept_values' => true, 'apply_to' => ['text']],
        'contains' => ['accept_values' => true, 'apply_to' => ['text']],
        'not_contains' => ['accept_values' => true, 'apply_to' => ['text']],
        'ends_with' => ['accept_values' => true, 'apply_to' => ['text']],
        'not_ends_with' => ['accept_values' => true, 'apply_to' => ['text']],
        'is_empty' => ['accept_values' => false, 'apply_to' => ['text']],
        'is_not_empty' => ['accept_values' => false, 'apply_to' => ['text']],
        'is_null' => ['accept_values' => false, 'apply_to' => ['text', 'number', 'numeric', 'date', 'select']],
        'is_not_null' => ['accept_values' => false, 'apply_to' => ['text', 'number', 'numeric', 'date', 'select']],
    ];

    /**
     * SQL Operators
     *
     * @var array
     */
    protected $operator_sql = [
        'is' => ['operator' => 'BETWEEN'],
        'was' => ['operator' => 'BETWEEN'],
        'equal' => ['operator' => '='],
        'not_equal' => ['operator' => '!='],
        'in' => ['operator' => 'IN'],
        'not_in' => ['operator' => 'NOT IN'],
        'less' => ['operator' => '<'],
        'less_or_equal' => ['operator' => '<='],
        'greater' => ['operator' => '>'],
        'greater_or_equal' => ['operator' => '>='],
        'between' => ['operator' => 'BETWEEN'],
        'not_between' => ['operator' => 'NOT BETWEEN'],
        'begins_with' => ['operator' => 'LIKE',     'prepend' => '%'],
        'not_begins_with' => ['operator' => 'NOT LIKE', 'prepend' => '%'],
        'contains' => ['operator' => 'LIKE',     'append' => '%', 'prepend' => '%'],
        'not_contains' => ['operator' => 'NOT LIKE', 'append' => '%', 'prepend' => '%'],
        'ends_with' => ['operator' => 'LIKE',     'append' => '%'],
        'not_ends_with' => ['operator' => 'NOT LIKE', 'append' => '%'],
        'is_empty' => ['operator' => '='],
        'is_not_empty' => ['operator' => '!='],
        'is_null' => ['operator' => 'NULL'],
        'is_not_null' => ['operator' => 'NOT NULL'],
    ];

    /**
     * The operator that needs array
     *
     * @var array
     */
    protected $needs_array = [
        'IN', 'NOT IN', 'BETWEEN', 'NOT BETWEEN',
    ];

    /**
     * Determine if an operator (LIKE/IN) requires an array.
     *
     *
     * @return bool
     */
    protected function operatorRequiresArray($operator)
    {
        return in_array($operator, $this->needs_array);
    }

    /**
     * Check whether the filter is using the date IS operator
     *
     * @param  \stdClass  $filter
     * @return bool
     */
    public function isDateIsOperator($filter)
    {
        return $this->isDateFilter($filter) && $filter->operator === 'is';
    }

    /**
     * Check whether the filter is using the date WAS operator
     *
     * @param  \stdClass  $filter
     * @return bool
     */
    public function isDateWasOperator($filter)
    {
        return $this->isDateFilter($filter) && $filter->operator === 'was';
    }

    /**
     * Check whether the given filter is date
     *
     * @param  \stdClass  $filter
     * @return bool
     */
    public function isDateFilter($filter)
    {
        return $filter->type == 'date';
    }

    /**
     * Determine if an operator is NULL/NOT NULL
     *
     *
     * @return bool
     */
    protected function operatorIsNull($operator)
    {
        return ($operator == 'NULL' || $operator == 'NOT NULL') ? true : false;
    }

    /**
     * Check if the given attribute exists on filters lists
     *
     * E.q. custom field that was previously applied to a filter and later
     * is deleted won't exists, we need to skip such query
     *
     * @param  array|null  $fields
     * @param  \App\Core\Application\Filters\Filter  $field
     *
     * @throws QueryBuilderException
     */
    public function fieldExistsAndItsAllowed($attribute, $fields)
    {
        return is_array($fields) && in_array($attribute, $fields);
    }

    /**
     * Enforce whether the value for a given field is the correct type
     *
     * @param  bool  $requireArray value must be an array
     * @param  mixed  $value the value we are checking against
     * @param  string  $field the field that we are enforcing
     * @return mixed value after enforcement
     *
     * @throws QueryBuilderException if value is not a correct type
     */
    protected function enforceArrayOrString($requireArray, $value, $field)
    {
        $this->checkFieldIsAnArray($requireArray, $value, $field);

        if (!$requireArray && is_array($value)) {
            return $this->convertArrayToFlatValue($field, $value);
        }

        return $value;
    }

    /**
     * Ensure that a given field is an array if required.
     *
     * @see enforceArrayOrString
     *
     * @param  bool  $requireArray
     * @param  string  $field
     *
     * @throws QueryBuilderException
     */
    protected function checkFieldIsAnArray($requireArray, $value, $field)
    {
        if ($requireArray && !is_array($value)) {
            throw new Exception("Field ($field) should be an array, but it isn't.");
        }
    }

    /**
     * Convert an array with just one item to a string.
     *
     * In some instances, and array may be given when we want a string.
     *
     * @see enforceArrayOrString
     *
     * @param  string  $field
     * @return mixed
     *
     * @throws QueryBuilderException
     */
    protected function convertArrayToFlatValue($field, $value)
    {
        if (count($value) !== 1) {
            throw new QueryBuilderException("Field ($field) should not be an array, but it is.");
        }

        return $value[0];
    }

    /**
     * Convert a Datetime field to Carbon items to be used for comparisons.
     *
     * @param  string|array  $value
     * @param  \App\Core\Application\Filters\Filter  $filter
     * @return \App\Core\Application\Date\Carbon|array
     */
    protected function getDateCarbonValueByRequestedValue($value, $filter)
    {
        // Is between
        if (is_array($value)) {
            return array_map(function ($date) use ($filter) {
                return $filter instanceof DateTime ? Carbon::fromCurrentToAppTimezone($date) : Carbon::parse($date);
            }, $value);
        }

        return $this->getDateCarbonValueByRequestedValue([$value], $filter)[0];
    }

    /**
     * Append or prepend a string to the query if required.
     *
     * @param  bool  $requireArray value must be an array
     * @param  mixed  $value the value we are checking against
     * @param  mixed  $sqlOperator
     * @return mixed $value
     */
    protected function appendOperatorIfRequired($requireArray, $value, $sqlOperator)
    {
        if (!$requireArray) {
            if (isset($sqlOperator['append'])) {
                $value = $sqlOperator['append'] . $value;
            }
            if (isset($sqlOperator['prepend'])) {
                $value = $value . $sqlOperator['prepend'];
            }
        }

        return $value;
    }

    /**
     * Make query when the rule should count the relation
     *
     * @param  \App\Core\Application\Filters\CountableRelation | \App\Core\Application\Filters\Filter | null  $filter
     * @param  string  $operator
     * @param  int  $value
     * @param  string  $condition
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function makeQueryWhenCountableRelation(Builder $builder, $filter, $operator, $value, $condition, Closure $callback = null)
    {
        return $builder->has($filter->getCountableRelation(), $operator, $value, $condition, $callback);
    }

    /**
     * Create a 'date' query when required.
     *
     * @param  \Illuminate\Core\Application\Filters\Filter  $filter
     * @param  string  $sqlOperator
     * @param  array|\Carbon  $value
     * @param  string  $condition
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function makeQueryWhenDate(Builder $builder, $filter, stdClass $rule, $sqlOperator, $value, $condition)
    {
        // When querying dates by equal or not equal we must provide a proper format
        // https://stackoverflow.com/questions/1754411/how-to-select-date-from-datetime-column
        // Because with Carbon, will search like e.q. where date = Y-m-d 00:00:00
        if (in_array($rule->operator, ['equal', 'not_equal'])) {
            if ($filter instanceof DateTime) {
                return $this->makeQueryWhenArray(
                    $builder,
                    $rule,
                    $this->operator_sql[$rule->operator === 'equal' ? 'between' : 'not_between']['operator'],
                    // 24 hours - 1 second e.q. 2020-03-29 22:00:00 - 2020-03-30 21:59:00
                    [$value, $value->copy()->addSeconds((24 * 60 * 60) - 1)],
                    $condition
                );
            } elseif ($filter instanceof Date) {
                $value = $value->format('Y-m-d');
            }
        } elseif (in_array($rule->operator, ['less', 'less_or_equal', 'greater', 'greater_or_equal'])) {
            if ($filter instanceof DateTime) {
                // For less and greater_or_equal there is no need for formatting
                if (
                    $rule->operator === 'less' ||
                    $rule->operator === 'greater_or_equal'
                ) {
                } else {
                    // e.q. 2021-04-21 => 2021-04-21 21:59:59
                    $value->addSeconds((24 * 60 * 60) - 1);
                }
            } elseif ($filter instanceof Date) {
                $value = $value->format('Y-m-d');
            }
        }

        return $this->convertToQuery($builder, $rule, $value, $sqlOperator, $condition);
    }

    /**
     * makeQuery, for arrays.
     *
     * Some types of SQL Operators (ie, those that deal with lists/arrays) have specific requirements.
     * This function enforces those requirements.
     *
     * @param  string  $sqlOperator
     * @param  string  $condition
     * @return \Illuminate\Database\Eloquent\Builder
     *
     * @throws QueryBuilderException
     */
    public function makeQueryWhenArray(Builder $builder, stdClass $rule, $sqlOperator, array $value, $condition)
    {
        if ($sqlOperator == 'IN' || $sqlOperator == 'NOT IN') {
            return $this->makeArrayQueryIn($builder, $rule, $sqlOperator, $value, $condition);
        } elseif ($sqlOperator == 'BETWEEN' || $sqlOperator == 'NOT BETWEEN') {
            return $this->makeArrayQueryBetween($builder, $rule, $sqlOperator, $value, $condition);
        }

        throw new QueryBuilderException('makeQueryWhenArray could not return a value');
    }

    /**
     * Create a 'null' query when required.
     *
     * @param  string  $sqlOperator
     * @param  string  $condition
     * @return \Illuminate\Database\Eloquent\Builder
     *
     * @throws QueryBuilderException when SQL operator is !null
     */
    public function makeQueryWhenNull(Builder $builder, stdClass $rule, $sqlOperator, $condition)
    {
        if ($sqlOperator == 'NULL') {
            return $builder->whereNull($this->getQueryColumn($rule, $builder), $condition);
        } elseif ($sqlOperator == 'NOT NULL') {
            return $builder->whereNotNull($this->getQueryColumn($rule, $builder), $condition);
        }

        throw new QueryBuilderException('makeQueryWhenNull was called on an SQL operator that is not null');
    }

    /**
     * makeArrayQueryIn, when the query is an IN or NOT IN...
     *
     * @see makeQueryWhenArray
     *
     * @param  string  $operator
     * @param  string  $condition
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function makeArrayQueryIn(Builder $builder, stdClass $rule, $operator, array $value, $condition)
    {
        // If nothing is checked just return the query
        if (empty($value) && $rule->type == 'checkbox' || empty($value) && $rule->type == 'multi-select') {
            return $builder;
        }

        if ($operator == 'NOT IN') {
            return $builder->whereNotIn($this->getQueryColumn($rule, $builder), $value, $condition);
        }

        return $builder->whereIn($this->getQueryColumn($rule, $builder), $value, $condition);
    }

    /**
     * makeArrayQueryBetween, when the query is a BETWEEN or NOT BETWEEN...
     *
     * @see makeQueryWhenArray
     *
     * @param string operator the SQL operator used. [BETWEEN|NOT BETWEEN]
     * @param  string  $condition
     * @return \Illuminate\Database\Eloquent\Builder
     *
     * @throws QueryBuilderException when more then two items given for the between
     */
    public function makeArrayQueryBetween(Builder $builder, stdClass $rule, $operator, array $value, $condition)
    {
        if (count($value) !== 2) {
            throw new QueryBuilderException(
                Str::title($rule->attribute) . ' must be an array with only two items.'
            );
        } elseif ($value[1] == '') {
            throw new QueryBuilderException(
                'Please select the between value for ' . Str::title($rule->attribute) . ' filter.'
            );
        }

        if ($operator == 'NOT BETWEEN') {
            return $builder->whereNotBetween($this->getQueryColumn($rule, $builder), $value, $condition);
        }

        // dd($value, $builder->whereBetween($this->getQueryColumn($rule, $builder), $value, $condition)->toSql());

        return $builder->whereBetween($this->getQueryColumn($rule, $builder), $value, $condition);
    }

    /**
     * Get the column for the query from the given rule
     *
     * @param  \stdClass  $rule
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @return string|\Illuminate\Database\Query\Expression
     */
    protected function getQueryColumn($rule, $builder)
    {
        if (!$rule->attribute instanceof Expression) {
            return $builder->qualifyColumn($rule->attribute);
        }

        return $rule->attribute;
    }
}
