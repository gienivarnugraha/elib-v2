<?php

namespace App\Core\Facades;

use App\Core\Application\Date\Format as BaseFormat;
use Illuminate\Support\Facades\Facade;

class Format extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return BaseFormat::class;
    }
}
