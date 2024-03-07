<?php

namespace App\Core\Http\Request;

class UpdateResourceRequest extends ResourcefulRequest
{
    /**
     * Get the fields for the current request
     *
     * @return \App\Core\Application\Fields\FieldsCollection
     */
    public function fields()
    {
        $this->resource()->setModel($this->record());

        return $this->resource()->resolveUpdateFields();
    }
}
