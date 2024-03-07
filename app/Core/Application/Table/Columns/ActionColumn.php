<?php

namespace App\Core\Application\Table\Columns;

use App\Core\Application\Table\Column;

class ActionColumn extends Column
{
    /**
     * This column is not sortable
     */
    public bool $sortable = false;

    /**
     * Initialize new ActionColumn instance.
     */
    public function __construct(string $label = null)
    {
        // Set the attribute to null to prevent showing on re-order table options
        parent::__construct(null, $label);
    }
}
