<?php

namespace App\Core\Http\Request;

class CreateResourceRequest extends ResourcefulRequest
{
    /**
     * Get the fields for the current request
     *
     * @return \App\Core\Application\Fields\FieldsCollection
     */
    public function fields()
    {
        return $this->resource()->resolveCreateFields();
    }
}
