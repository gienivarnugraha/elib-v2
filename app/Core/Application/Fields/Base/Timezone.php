<?php

namespace App\Core\Application\Fields\Base;

use App\Core\Application\Fields\Field;
use App\Core\Facades\Timezone as Facade;
use App\Core\Rules\ValidTimezoneCheckRule;

class Timezone extends Field
{
    /**
     * Field component
     *
     * @var string
     */
    public $component = 'timezone-field';

    /**
     * Initialize Timezone field
     *
     * @param  string  $attribute
     * @param  string|null  $label
     */
    public function __construct($attribute, $label = null)
    {
        parent::__construct($attribute, $label ?? __('app.timezone'));

        $this->rules(new ValidTimezoneCheckRule)
            ->provideSampleValueUsing(fn () => array_rand(array_flip(tz()->all())));
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
        $table->string($fieldId)->nullable();
    }

    /**
     * Provide the options intended for Zapier
     */
    public function jsonSerialize(): array
    {
        return array_merge(parent::jsonSerialize(), [
            'timezones' => Facade::toArray(),
        ]);
    }
}
