<?php

namespace App\Core\Application\Filters\Fields;

use App\Core\Application\Filters\Filter;

class Numeric extends Filter
{
    /**
     * Defines a filter type
     */
    public function type(): string
    {
        return 'numeric';
    }
}
