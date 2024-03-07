<?php

namespace App\Core\Application\Fields\Base;

class Number extends Text
{
    /**
     * Input type
     */
    public string $inputType = 'number';

    /**
     * Initialize Numeric field
     *
     * @param  string  $attribute
     * @param  string|null  $label
     */
    public function __construct($attribute, $label = null)
    {
        parent::__construct($attribute, $label);

        $this->rules(['nullable', 'integer'])
            ->provideSampleValueUsing(fn () => rand(1990, date('Y')));
    }
}
