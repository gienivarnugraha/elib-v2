<?php

namespace App\Core\Application\Filters;

use App\Core\Application\Fields\Base\Checkbox;
use App\Core\Application\Fields\Base\MultiSelect;
use App\Core\Application\Fields\Optionable;
use App\Core\QueryBuilder\Parser;
use App\Core\QueryBuilder\ParserTrait;
use App\Core\Traits\Authorizeable;
use App\Core\Traits\Makeable;
use App\Core\Traits\Metable;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use JsonSerializable;

class Filter implements Arrayable, JsonSerializable
{
    use Authorizeable,
        Makeable,
        Metable,
        ParserTrait;

    /**
     * Query builder rule component
     *
     * @var null|string
     */
    public $component = null;

    /**
     * Filter attribute/rule
     *
     * @var string
     */
    public $attribute;

    /**
     * Filter label
     *
     * @var string|null
     */
    public $label;

    /**
     * Whether to include null operators
     *
     * @var bool
     */
    public $withNullOperators = false;

    /**
     * Filter operators
     *
     * @var array
     */
    public $filterOperators = [];

    /**
     * Exclude operators
     *
     * @var array
     */
    public $excludeOperators = [];

    /**
     * @var null|callable
     */
    public $tapCallback;

    /**
     * @var null|callable
     */
    protected $callback;

    /**
     * Filter current operator
     *
     * @var string|null
     */
    protected $operator;

    /**
     * Filter current value
     *
     * @var array|string|null
     */
    protected $value;

    /**
     * Custom display as text
     *
     * @var string|array|null
     */
    protected $displayAs = null;

    /**
     * @param  string  $attribute
     * @param  string|null  $label
     * @param  null|array  $operators
     */
    public function __construct($attribute, $label = null, $operators = null)
    {
        $this->attribute = $attribute;
        $this->label = $label;

        is_array($operators) ? $this->setOperators($operators) : $this->determineOperators();
    }

    /**
     * Filter type from available filter types developed for front end
     */
    public function type(): ?string
    {
        return null;
    }



    /**
     * Get the filter component
     */
    public function component(): string
    {
        return $this->component ? $this->component :  'v-' . $this->type() . '-rule';
    }

    /**
     * Add custom query handler instead of using the query builder parser
     */
    public function query(callable $callback): static
    {
        $this->callback = $callback;

        return $this;
    }

    /**
     * Add query tap callback
     */
    public function tapQuery(callable $callback): static
    {
        $this->tapCallback = $callback;

        return $this;
    }

    /**
     * Apply the filter when custom query callback is provided
     *
     * @param  mixed  $value
     *  @param  string  $condition
     *  @param  array  $sqlOperator
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function apply(Builder $builder, $value, $condition, $sqlOperator, $rule, Parser $parser)
    {
        return call_user_func(
            $this->callback,
            $builder,
            $value,
            $condition,
            $sqlOperator,
            $rule,
            $parser
        );
    }

    /**
     * Add display
     *
     * @param  mixed  $value
     */
    public function displayAs($value): static
    {
        $this->displayAs = $value;

        return $this;
    }

    /**
     * Check whether the filter is optionable
     */
    public function isOptionable(): bool
    {
        if ($this->isMultiOptionable()) {
            return true;
        }

        return $this instanceof Optionable;
    }

    /**
     * Check whether the filter is multi optionable
     */
    public function isMultiOptionable(): bool
    {
        return $this instanceof MultiSelect || $this instanceof Checkbox;
    }

    /**
     * Check whether the filter has custom callback
     */
    public function hasCustomQuery(): bool
    {
        return !is_null($this->callback);
    }

    /**
     * Set the filter current value
     *
     * @param  string|array  $value
     */
    public function setValue($value): static
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get the filter active value
     *
     * @return string|array|null
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Get the filter current operator
     *
     * @return string|null
     */
    public function getOperator()
    {
        return $this->operator;
    }

    /**
     * Set the filter current operator
     *
     * @param  string  $operator
     */
    public function setOperator($operator): static
    {
        $this->operator = $operator;

        return $this;
    }

    /**
     * Exclude operators
     *
     * @param  array  $operator
     */
    public function withoutOperators($operator): static
    {
        $this->excludeOperators = is_array($operator) ? $operator : func_get_args();

        return $this;
    }

    /**
     * Whether to include null operators
     *
     * @param  bool  $bool
     */
    public function withNullOperators($bool = true): static
    {
        $this->withNullOperators = $bool;

        return $this;
    }

    /**
     * Get the fillter operators
     *
     * @return array
     */
    protected function getOperators()
    {
        $operators = array_unique($this->filterOperators);

        if ($this->withNullOperators === false) {
            $operators = array_diff($operators, ['is_null', 'is_not_null']);
        }

        return array_values(
            array_diff(
                $operators,
                $this->excludeOperators
            )
        );
    }

    /**
     * Set custom operators
     */
    public function setOperators(array $operators): static
    {
        $this->filterOperators = $operators;

        return $this;
    }

    /**
     * Get operators options
     *
     * @return array
     */
    protected function operatorsOptions()
    {
        $options = [];
        foreach ($this->getOperators() as $operator) {
            $method = Str::studly(str_replace('.', '_', $operator)) . 'OperatorOptions';

            if (method_exists($this, $method)) {
                $options[$operator] = $this->{$method}() ?: [];
            }
        }

        return $options;
    }

    /**
     * Auto determines the operators on initialize based on ParserTrait
     *
     * @return void
     */
    private function determineOperators()
    {
        foreach ($this->operators as $operator => $data) {
            if (in_array($this->type(), $data['apply_to'])) {
                $this->filterOperators[] = $operator;
            }
        }
    }

    /**
     * Create rule able array from the filter
     *
     * @return array
     */
    public function toArray()
    {
        return $this->rules();
    }

    /**
     * Get the filter builder data
     *
     * @return array
     */
    public function rules()
    {
        return array_filter([
            'type' => $this->type(),
            'attribute' => $this->attribute,
            'operator' => $this->operator,
            'operand' => $this instanceof OperandFilter ? $this->operand : null,
            'value' => $this->value,
        ]);
    }

    /**
     * jsonSerialize
     */
    public function jsonSerialize(): array
    {
        return array_merge([
            'id' => $this->attribute,
            'label' => $this->label,
            'type' => $this->type(),
            'operators' => $this->getOperators(),
            'operatorsOptions' => $this->operatorsOptions(),
            'component' => $this->component(),
            'operands' => $this instanceof OperandFilter ? $this->getOperands() : [],
            'has_authorization' => $this->hasAuthorization(),
            'display_as' => $this->displayAs,
            'rules' => $this->rules(),
        ], $this->meta());
    }
}
