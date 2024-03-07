<?php

namespace App\Core\Application\Fields\Base;

class Percent extends Text
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

        $this->rules(['nullable'])
            ->saveUsing(function ($request, $requestAttribute, $value, $field) {
                if ($request->has($requestAttribute)) {
                    $value = (is_int($value) || is_float($value) || (is_numeric($value) && ! empty($value))) ? $value : 0;
                }

                return [$field->attribute => $value];
            })
            ->inputGroupAppend('%')
            ->withMeta(['attributes' => [
                'max' => 100,
                'decimal' => true,
            ]])
            ->provideSampleValueUsing(fn () => rand(0, 50));
    }
}
