<?php

namespace App\Core\Application\Fields\Base;

use App\Core\Application\Fields\Field;
use App\Core\Contracts\Fields\Dateable;
use App\Core\Facades\Application;
use App\Core\Facades\Format;
use App\Core\Resources\Http\ResourceRequest;
use App\Core\Table\DateTimeColumn;

class DateTime extends Field implements Dateable
{
    /**
     * Field component
     *
     * @var string
     */
    public $component = 'date-time-field';

    /**
     * Boot the field
     *
     * @return void
     */
    public function boot()
    {
        $this->rules(['nullable', 'date'])
            ->provideSampleValueUsing(fn () => date('Y-m-d H:i:s'));
    }

    /**
     * Handle the resource record "creating" event
     *
     * @param  \App\Core\Models\Model  $model
     * @return void
     */
    public function recordCreating($model)
    {
        if (! Application::isImportInProgress() || ! $model->usesTimestamps()) {
            return;
        }

        $timestampAttrs = [$model->getCreatedAtColumn(), $model->getUpdatedAtColumn()];
        $request = app(ResourceRequest::class);

        if ($request->has($this->requestAttribute()) &&
            in_array($this->attribute, $timestampAttrs) &&
            $model->isGuarded($this->attribute)) {
            $model->{$this->attribute} = $request->input($this->attribute);
        }
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
        $table->dateTime($fieldId)->nullable();
    }

    /**
     * Resolve the displayable field value
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return string|null
     */
    public function resolveForDisplay($model)
    {
        return Format::dateTime($model->{$this->attribute});
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
     */
    public function indexColumn(): DateTimeColumn
    {
        return new DateTimeColumn($this->attribute, $this->label);
    }
}
