<?php

namespace App\Core\Facades;

use App\Core\Core;
use Illuminate\Support\Facades\Facade;

class Application extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return Core::class;
    }
}
