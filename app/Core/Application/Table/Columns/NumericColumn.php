<?php

namespace App\Core\Application\Table\Columns;

use App\Core\Application\Table\Column;
use App\Core\Facades\Application;

class NumericColumn extends Column
{
    /**
     * Indicates whether the field has currency
     */
    public bool $withCurrency = true;

    /**
     * Initialize new NumericColumn instance.
     *
     * @param  array  $params
     */
    public function __construct(...$params)
    {
        parent::__construct(...$params);

        // Do not use queryAs as it's not supported (tested) for this type of column
        $this->displayAs(function ($model) {
            $value = $model->{$this->attribute};

            if (! empty($value)) {
                if (! $this->withCurrency) {
                    return $value;
                }

                return money($value, Application::currency(), true)->format();
            }

            return '--';
        });
    }

    /**
     * Set that the value should be display with currency
     */
    public function withCurrency(bool $value): static
    {
        $this->withCurrency = $value;

        return $this;
    }
}
