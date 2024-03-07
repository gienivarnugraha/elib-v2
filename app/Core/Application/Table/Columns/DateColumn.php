<?php

namespace App\Core\Application\Table\Columns;

use App\Core\Application\Table\Column;
use App\Core\Facades\Format;

class DateColumn extends Column
{
    /**
     * Initialize new DateColumn instance.
     */
    public function __construct(string $attribute = null, string $label = null)
    {
        parent::__construct($attribute, $label);

        $this->displayAs(fn ($model) => Format::date($model->{$this->attribute}));
    }
}
