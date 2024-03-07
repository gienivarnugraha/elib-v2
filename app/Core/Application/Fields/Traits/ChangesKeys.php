<?php

namespace App\Core\Application\Fields\Traits;

trait ChangesKeys
{
    /**
     * From where the value key should be taken
     */
    public string $valueKey = 'value';

    /**
     * From where the label key should be taken
     */
    public string $labelKey = 'text';

    /**
     * Set custom key for value
     *
     *
     * @return mixed
     */
    public function valueKey(string $key): static
    {
        $this->valueKey = $key;

        return $this;
    }

    /**
     * Set custom label key
     *
     *
     * @return mixed
     */
    public function labelKey(string $label): static
    {
        $this->labelKey = $label;

        return $this;
    }
}
