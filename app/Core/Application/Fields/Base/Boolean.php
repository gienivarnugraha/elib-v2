<?php

namespace App\Core\Application\Fields\Base;

use App\Core\Application\Fields\Field;
use App\Core\Application\Table\Columns\BooleanColumn;

class Boolean extends Field
{
    /**
     * Field component
     *
     * @var string
     */
    public $component = 'v-switch';

    /**
     * Checkbox checked value
     */
    public mixed $trueValue = true;

    /**
     * Checkbox unchecked value
     */
    public mixed $falseValue = false;

    /**
     * Custom boot function
     *
     * @return void
     */
    public function boot()
    {
        $this->provideSampleValueUsing(fn () => 1);
    }

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
     * Resolve the field value for export
     * The export value should be in the original database value
     * not e.q. Yes or No
     *
     * @param  \App\Core\Models\Model  $model
     * @return string|null
     */
    public function resolveForExport($model)
    {
        return $this->resolve($model);
    }

    /**
     * Resolve the displayable field value
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return string|null
     */
    public function resolveForDisplay($model)
    {
        $value = parent::resolveForDisplay($model);

        return $value === $this->trueValue ? __('app.yes') : __('app.no');
    }

    /**
     * jsonSerialize
     */
    public function jsonSerialize(): array
    {
        return array_merge(parent::jsonSerialize(), [
            'trueValue' => $this->trueValue,
            'falseValue' => $this->falseValue,
        ]);
    }
}
