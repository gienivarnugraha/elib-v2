<?php

namespace App\Core\Application\Fields\Base;

use App\Core\Application\Fields\Field;
use App\Core\Application\Table\Columns\DateColumn;
use App\Core\Contracts\Fields\Dateable;
use App\Core\Facades\Format;

class Date extends Field implements Dateable
{
    /**
     * Field component
     *
     * @var string
     */
    public $component = 'date-field';

    /**
     * Boot the field
     *
     * @return void
     */
    public function boot()
    {
        $this->rules(['nullable', 'date'])
            ->provideSampleValueUsing(fn () => date('Y-m-d'));
    }

    /**
     * Create the custom field value column in database
     *
     * @param  \Illuminate\Database\Schema\Blueprint  $table
     * @param  string  $fieldId
     * @return void
     */
    public static function createValueColumn($table, $fieldId)
    {
        $table->date($fieldId)->nullable();
    }

    /**
     * Resolve the displayable field value
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return string|null
     */
    public function resolveForDisplay($model)
    {
        return Format::date($model->{$this->attribute});
    }

    /**
     * Resolve the field value for export.
     *
     * @param  \App\Core\Models\Model  $model
     * @return string
     */
    public function resolveForExport($model)
    {
        return $model->{$this->attribute};
    }

    /**
     * Provide the column used for index
     *
     * @return \App\Core\Table\DateColumn
     */
    public function indexColumn(): DateColumn
    {
        return new DateColumn($this->attribute, $this->label);
    }
}
