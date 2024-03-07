<?php

namespace App\Core\Application\Table\Columns;

use App\Core\Application\Table\Column;
use App\Core\Facades\Format;

class DateTimeColumn extends Column
{
    /**
     * Initialize new DateTimeColumn instance.
     */
    public function __construct(string $attribute = null, string $label = null)
    {
        parent::__construct($attribute, $label);

        $this->displayAs(fn ($model) => Format::dateTime($model->{$this->attribute}));
    }
}
