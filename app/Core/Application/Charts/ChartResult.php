<?php

namespace App\Core\Application\Charts;


use Closure;
use JsonSerializable;

class ChartResult implements JsonSerializable
{
    /**
     * Create a new partition result instance.
     *
     * @param array $value
     *
     * @return void
     */
    public function __construct(public array $value)
    {
    }

    /**
     * Format the labels for the partition result.
     *
     * @param \Closure $callback
     *
     * @return static
     */
    public function label(Closure $callback): static
    {
        $this->value = collect($this->value)->mapWithKeys(function ($value, $label) use ($callback) {
            return [$callback($label) => $value];
        })->all();

        return $this;
    }

    /**
     * Prepare the metric result for JSON serialization.
     *
     * @return array
     */
    public function jsonSerialize(): array
    {
        return collect($this->value)->map(function ($value, $label) {
            return array_filter([
                'label' => $label,
                'value' => $value,
            ], function ($value) {
                return !is_null($value);
            });
        })->values()->all();
    }
}
