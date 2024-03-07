<?php

namespace App\Core\QueryBuilder;

use App\Core\Application\Filters\CountableRelation;
use App\Core\Application\Filters\Filter;
use App\Core\QueryBuilder\Exceptions\QueryBuilderException;
use App\Core\Traits\ProvidesBetweenDateArgumentsViaString;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use stdClass;

class Parser
{
    use ParserTrait,
        ProvidesBetweenDateArgumentsViaString;

    /**
     * Initialize new Parser instance.
     *
     * @param  \Illimunate\Support\Collection  $filters
     */
    public function __construct(protected Collection $filters)
    {
    }

    /**
     * Check if the given rules are valid
     *
     * @param  \strClas  $rules
     * @return bool
     */
    public static function validate($rules)
    {
        if (blank($rules)) {
            return false;
        }

        // This can happen if the querybuilder has no rules...
        if (!isset($rules) || !is_array($rules)) {
            return false;
        }
    }

    /**
     * Build a query based on JSON that has been passed into the function, onto the builder passed into the function.
     *
     * @param  \stdClass  $query
     * @return \Illuminate\Database\Eloquent\Builder
     *
     * @throws QueryBuilderException
     */
    public function parse($query, Builder $builder)
    {
        foreach ($query as $rule) {
            /*
            * The field must exist in our list and be allowed.
            */
            if (!$this->fieldExistsAndItsAllowed($rule->attribute, $this->whitelistedRules())) {
                continue;
            }

            $builder = $this->makeQuery($builder, $rule);
        }

        return $builder;
    }

    /**
     * Get the while listed attributes
     *
     * @return array
     */
    protected function whitelistedRules()
    {
        return $this->filters->map(function ($filter) {
            return $filter->attribute;
        })->all();
    }

    /**
     * Take a particular rule and make build something that the QueryBuilder would be proud of.
     *
     * Make sure that all the correct fields are in the rule object then add the expression to
     * the query that was given by the user to the QueryBuilder.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     *
     * @throws QueryBuilderException
     */
    public function makeQuery(Builder $builder, stdClass $rule)
    {
        $value = $this->getValueForQueryFromRule($rule);

        return $this->convertQBToQuery($builder, $rule, $value);
    }

    /**
     * Ensure that the value is correct for the rule, try and set it if it's not.
     *
     *
     * @return mixed
     *
     * @throws QueryBuilderException
     */
    protected function getValueForQueryFromRule(stdClass $rule)
    {
        /*
         * Make sure most of the common fields from the QueryBuilder have been added.
         */
        $value = $this->getRuleValue($rule);

        /*
         * If the SQL Operator is set not to have a value, make sure that we set the value to null.
         */
        if ($this->operators[$rule->operator]['accept_values'] === false) {
            return $this->operatorValueWhenNotAcceptingOne($rule);
        }

        /*
         * Convert the Operator (LIKE/NOT LIKE/GREATER THAN) given to us by QueryBuilder
         * into on one that we can use inside the SQL query
         */
        $sqlOperator = $this->operator_sql[$rule->operator];
        $operator = $sqlOperator['operator'];
        /*
         * \o/ Ensure that the value is an array only if it should be.
         */

        return $this->getCorrectValue($operator, $rule, $value);
    }

    /**
     * get a value for a given rule.
     *
     * throws an exception if the rule is not correct.
     *
     *
     * @return mixed
     *
     * @throws QueryBuilderException
     */
    protected function getRuleValue(stdClass $rule)
    {
        // Just before making a query for a rule, we want to make sure that the field operator and value are set
        if (!isset($this->operators[$rule->operator])) {
            throw new QueryBuilderException('Rule not correct');
        }

        return $rule->value;
    }

    /**
     * Give back the correct value when we don't accept one.
     *
     *
     * @return null|string
     */
    protected function operatorValueWhenNotAcceptingOne(stdClass $rule)
    {
        if ($rule->operator == 'is_empty' || $rule->operator == 'is_not_empty') {
            return '';
        }

        return null;
    }

    /**
     * Ensure that the value for a field is correct.
     *
     * Append/Prepend values for SQL statements, etc.
     *
     *
     * @return string
     *
     * @throws QueryBuilderException
     */
    protected function getCorrectValue($operator, stdClass $rule, $value)
    {
        $field = $rule->attribute;
        $sqlOperator = $this->operator_sql[$rule->operator];
        $requireArray = $this->operatorRequiresArray($operator);

        if ($this->isDateIsOperator($rule)) {
            $value = $this->getCorrectValueWhenIsDateIsOperator($value);
        } elseif ($this->isDateWasOperator($rule)) {
            $value = $this->getCorrectValueWhenIsDateWasOperator($value);
        } else {
            $value = $this->enforceArrayOrString($requireArray, $value, $field);

            if ($rule->type == 'date') {
                $value = $this->getDateCarbonValueByRequestedValue($value, $this->findFilterByRule($rule));
            }
        }

        return $this->appendOperatorIfRequired($requireArray, $value, $sqlOperator);
    }

    /**
     * Get between dates when rule is DATE and IS operator is selected
     *
     * @param  string  $value
     * @return array
     *
     * @throws QueryBuilderException
     */
    protected function getCorrectValueWhenIsDateIsOperator($value)
    {
        try {
            return $this->getBetweenArguments($value);
        } catch (Exception $e) {
            throw new QueryBuilderException($e->getMessage());
        }
    }

    /**
     * Get between dates when rule is DATE and WAS operator is selected
     *
     * @param  string  $value
     * @return array
     *
     * @throws QueryBuilderException
     */
    protected function getCorrectValueWhenIsDateWasOperator($value)
    {
        try {
            return $this->getBetweenArguments($value);
        } catch (Exception $e) {
            throw new QueryBuilderException($e->getMessage());
        }
    }

    /**
     * Find filter by a given rule
     *
     * @param  stdClass  $rule
     * @return \App\Innoclapps\Filters\Filter|null
     */
    protected function findFilterByRule($rule): ?Filter
    {
        return $this->filters->first(fn ($filter) => $filter->attribute === $rule->attribute);
    }

    /**
     * Convert an incomming rule from QueryBuilder to the Eloquent Querybuilder
     *
     * (This used to be part of makeQuery, where the name made sense, but I pulled it
     * out to reduce some duplicated code inside JoinSupportingQueryBuilder)
     *
     * @param  mixed  $value the value that needs to be queried in the database.
     * @param  string  $queryCondition and/or...
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function convertQBToQuery(Builder $builder, stdClass $rule, $value)
    {
        $filter = $this->findFilterByRule($rule);
        $condition = 'and'; // ? strtolower($queryCondition);

        /*
         * Convert the Operator (LIKE/NOT LIKE/GREATER THAN) given to us by QueryBuilder
         * into on one that we can use inside the SQL query
         */
        $sqlOperator = $this->operator_sql[$rule->operator];
        $operator = $sqlOperator['operator'];

        if ($filter->tapCallback) {
            call_user_func_array($filter->tapCallback, [$builder, $value, $condition, $rule]);
        }

        if ($this->ruleCountsRelation($filter)) {
            return $this->makeQueryWhenCountableRelation($builder, $filter, $operator, $value, $condition);
        } elseif ($this->isDateFilter($rule)) {
            return $this->makeQueryWhenDate($builder, $filter, $rule, $operator, $value, $condition);
        }

        return $this->convertToQuery($builder, $rule, $value, $operator, $condition);
    }

    /**
     * Determine if the given rule counts relationships
     *
     * @param  \App\Innoclapps\Filter\Filter  $rule
     * @return bool
     */
    public function ruleCountsRelation($rule)
    {
        return $rule instanceof CountableRelation && !empty($rule->getCountableRelation());
    }

    /**
     * Convert to regular query helper
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function convertToQuery(Builder $builder, $rule, $value, $operator, $condition)
    {
        if ($this->operatorRequiresArray($operator)) {
            return $this->makeQueryWhenArray($builder, $rule, $operator, $value, $condition);
        } elseif ($this->operatorIsNull($operator)) {
            return $this->makeQueryWhenNull($builder, $rule, $operator, $condition);
        }

        return $builder->where(
            $this->getQueryColumn($rule, $builder),
            $operator,
            $value,
            $condition
        );
    }
}
