<?php

namespace App\Core\Application\Fields;

use Illuminate\Support\Collection;

class FieldsCollection extends Collection
{
    /**
     * Find field by attribute
     *
     * @param  string  $attribute
     * @return null\App\Core\Fields\Field
     */
    public function find($attribute)
    {
        return $this->firstWhere('attribute', $attribute);
    }

    /**
     * Find field by request attribute
     *
     * @param  string  $attribute
     * @return null\App\Core\Fields\Field
     */
    public function findByRequestAttribute($attribute)
    {
        return $this->first(function ($field) use ($attribute) {
            return $field->requestAttribute() === $attribute;
        });
    }
}
