<?php

namespace App\Core\Application\Filters\Fields;

use App\Core\Application\Fields\Traits\ChangesKeys;
use App\Core\Application\Fields\Traits\HasOptions;
use App\Core\Application\Filters\Filter;

class Optionable extends Filter
{
    use ChangesKeys,
        HasOptions;
}
