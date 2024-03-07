<?php

namespace App\Core\Application\Fields\Base;

use App\Core\Application\Fields\Optionable;
use App\Core\Application\Fields\Traits\Selectable;

class Autocomplete extends Optionable
{
    use Selectable;

    /**
     * Field component
     *
     * @var string
     */
    public $component = 'v-autocomplete';
}
