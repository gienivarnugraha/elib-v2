<?php

namespace App\Core\Application\Table\Columns;

use App\Core\Application\Table\Column;

class BooleanColumn extends Column
{
    /**
     * Initialize new BooleanColumn instance.
     */
    public function __construct(string $attribute = null, string $label = null)
    {
        parent::__construct($attribute, $label);
    }

    /**
     * Checkbox checked value
     */
    public mixed $trueIcon = 'bx-check';

    /**
     * Checkbox unchecked value
     */
    public mixed $falseIcon = 'bx-x';

    /**
     * Checkbox checked value
     */
    public mixed $trueValue = true;

    /**
     * Checkbox unchecked value
     */
    public mixed $falseValue = false;

    /**
     * Data heading component
     */
    public string $component = 'v-boolean-column';

    /**
     * Checkbox checked value
     */
    public function trueValue(mixed $val): static
    {
        $this->trueValue = $val;

        return $this;
    }

    /**
     * Checkbox unchecked value
     */
    public function falseValue(mixed $val): static
    {
        $this->falseValue = $val;

        return $this;
    }

    /**
     * Checkbox checked value
     */
    public function trueIcon(mixed $val): static
    {
        $this->trueIcon = $val;

        return $this;
    }

    /**
     * Checkbox unchecked value
     */
    public function falseIcon(mixed $val): static
    {
        $this->falseIcon = $val;

        return $this;
    }

    /**
     * Additional column
     */
    public function jsonSerialize(): array
    {
        $meta = array_merge([
            'falseValue' => $this->falseValue,
            'trueValue' => $this->trueValue,
            'falseIcon' => $this->falseIcon,
            'trueIcon' => $this->trueIcon,
        ], $this->meta);

        return array_merge(parent::toArray(), ['meta' => $meta]);
    }
}
