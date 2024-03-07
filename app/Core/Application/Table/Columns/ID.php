<?php

namespace App\Core\Application\Table\Columns;

use App\Core\Application\Table\Column;

class ID extends Column
{
    /**
     * Initialize ID class
     */
    public function __construct(string $label = null, ?string $attribute = 'id')
    {
        parent::__construct($attribute, $label);

        // $this->minWidth('120px')->width('120px');
    }
}
