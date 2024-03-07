<?php

namespace App\Core\Application\Fields\Traits;

use App\Core\Contracts\Enum;
use App\Core\Resources\Resource;

trait HasOptions
{
    /**
     * Provided options
     *
     * @var mixed
     */
    public $options = [];

    /**
     * Provided options
     *
     * @var mixed
     */
    public $labelAsValue = false;

    /**
     * Add field options
     *
     * @param  array|callable|Illuminate\Support\Collection|App\Core\Resources\Resource  $options
     * @return static
     */
    public function options(mixed $options)
    {
        $this->options = $options;

        return $this;
    }


    public function makeLabelAsValue()
    {
        $this->labelAsValue = true;

        return $this;
    }

    /**
     * Resolve the fields options
     *
     * @return array
     */
    public function resolveOptions()
    {

        $options = with($this->options, function ($options) {
            if (is_string($options) && enum_exists($options)) {
                $options = $options::names();
                $this->makeLabelAsValue();
            } elseif ($options instanceof Resource) {
                $options = $options->repository()->orderBy(
                    $options::$orderBy,
                    $options::$orderByDir
                )->all();
            } elseif (is_callable($options)) {
                $options = $options();
            }

            return $options;
        });

        return collect($options)->map(function ($label, $value) {

            return isset($label[$this->valueKey]) ?
                $label :
                [
                    'text' => $label,
                    'value' => $this->labelAsValue ? $label : $value
                ];
        })->values()->all();
    }

    /**
     * Field additional meta
     */
    public function meta(): array
    {
        return array_merge([
            'valueKey' => $this->valueKey,
            'labelKey' => $this->labelKey,
            //'optionsViaResource' => $this->options instanceof Resource ? $this->options->name() : null,
            'options' => $this->resolveOptions(),
        ], $this->meta);
    }
}
