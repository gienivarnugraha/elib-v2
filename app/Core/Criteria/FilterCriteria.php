<?php

namespace App\Core\Criteria;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Core\QueryBuilder\Parser;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use App\Core\QueryBuilder\JoinRelationParser;
use App\Core\Contracts\Repository\CriteriaInterface;
use App\Core\Contracts\Repository\RepositoryInterface;

class FilterCriteria implements CriteriaInterface
{
    /**
     * The rules
     *
     * [ 'type' => 'text',
     *   'attribute' => 'name',
     *   'operator' => 'equal',
     *   'value' => 'john',
     * ]
     *
     * @var \Illuminate\Support\Collection
     */
    protected $rules;

    /**
     * The workable object
     *
     * @var \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Builder
     */
    protected $model;

    /**
     * @param  array|object|null  $rules The request rules
     * @param  \Illuminate\Support\Collection|array  $filters All resource available filters
     * @param \Illuminate\Http\Request
     */
    public function __construct(protected Request $request, protected Collection|array $filters)
    {
        $this->rules = $request->get('rules');
    }

    /**
     * Apply criteria in query repository
     *
     * @param  \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Builder  $model
     * @param  \App\Innoclapps\Contracts\Repository\RepositoryInterface  $repository
     * @return mixed
     */
    public function apply($model, RepositoryInterface $repository)
    {
        $this->prepareRules();

        if (is_null($this->rules)) {
            return $model;
        }

        $this->model = $model;

        $this->setSpecialValueMe($this->rules);

        return $this->model->where(function ($builder) use ($model) {

            $this->createParser()->parse(
                $this->rules,
                $builder
            );

            // On the parent model query remove any global scopes
            // that are removed from the where builder instance
            // e.q. soft deleted when calling onlyTrashed
            $model->withoutGlobalScopes($builder->removedScopes());
        });
    }

    /**
     * Prepare the rules for the parser
     *
     * @return void
     */
    protected function prepareRules()
    {
        $rules = $this->rules;

        return $this->rules = is_array($rules) ? Arr::toObject($rules) : $rules;
    }

    /**
     * Create the filters parser
     *
     * @return App\Innoclapps\QueryBuilder\Parser
     */
    public function createParser()
    {
        return $this->hasRulesRelations($this->rules)
            ? new JoinRelationParser($this->filters, $this->prepareParserJoinFields($this->rules))
            : new Parser($this->filters);
    }

    /**
     * Check whether the rules from the requests includes a relation
     *
     * @param  object  $rules
     * @return bool
     */
    protected function hasRulesRelations($rules)
    {
        foreach ($rules as $rule) {
            return isset($rule->attribute) && $this->isFieldRelation($rule->attribute);
        }
    }

    /**
     * Check if field is relation e.q. contact.first_name
     *
     * @param  string  $name QueryBuilder Rule ID
     * @return bool
     */
    protected function isFieldRelation($name)
    {
        if (str_contains($name, '.')) {
            // Perhaps is e.q. companies.name with table prefix
            $ruleArray = array_reverse(explode('.', $name));
            $relation = end($ruleArray);

            // Not defined, not relation
            return method_exists($this->model->getModel(), $relation);
        }

        return false;
    }

    /**
     * Prepares the join fields for the parser
     *
     * @param  \stdClass  $rules
     * @return array
     */
    protected function prepareParserJoinFields($rules)
    {
        $retVal = [];

        foreach ($rules as $rule) {
            if ($relationJoinData = $this->getRelationFieldDataForQuery($rule->attribute)) {
                $parentModel = $this->model->getModel();
                $relationModel = $parentModel->{$relationJoinData['relation']}()->getModel();

                $retVal[$rule->attribute] = [];

                $retVal[$rule->attribute]['relation'] = $relationJoinData['relation'];
                $retVal[$rule->attribute]['from_table'] = $parentModel->getConnection()->getTablePrefix() . $parentModel->getTable();
                $retVal[$rule->attribute]['from_col'] = Str::singular($relationJoinData['relation']) . '_' . $relationModel->getKeyName();
                $retVal[$rule->attribute]['to_table'] = $parentModel->getConnection()->getTablePrefix() . $relationModel->getTable();
                $retVal[$rule->attribute]['to_col'] = $relationModel->getKeyName();
                $retVal[$rule->attribute]['to_value_column'] = $relationJoinData['field'];
            }
        }

        return $retVal;
    }


    /**
     * Set the special value me to the actual logged in user id
     *
     * This is only applied for User filter
     *
     * @return void
     */
    protected function setSpecialValueMe($rules)
    {
        foreach ($rules as $rule) {
            if ($this->isUserSpecialRule($rule)) {
                $rule->value = Auth::id();
            }
        }
    }

    /**
     * Check whether the given rule is the special user file
     *
     * @param \stdClass $rule
     *
     * @return boolean
     */
    protected function isUserSpecialRule($rule)
    {
        return isset($rule) && $rule->value === 'me';
    }

    /**
     * Get relation data to be used in query has
     *
     *    return $query->whereHas($relation, function ($query) use ($field, $operator, $value, $condition) {
     *       $query->where($field, $operator, $value, $condition);
     *    });
     *
     * @param  string  $name QueryBuilder Rule ID
     * @return array
     */
    protected function getRelationFieldDataForQuery($name)
    {
        if (!$this->isFieldRelation($name)) {
            return false;
        }

        $explode = explode('.', $name);
        $field = array_pop($explode);
        $relation = implode('.', $explode);

        return ['field' => $field, 'relation' => $relation];
    }
}
