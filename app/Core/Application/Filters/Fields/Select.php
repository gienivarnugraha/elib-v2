<?php

namespace App\Core\Application\Filters\Fields;

class Select extends Optionable
{
    /**
     * Defines a filter type
     */
    public function type(): string
    {
        return 'select';
    }
}
