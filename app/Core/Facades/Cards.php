<?php

namespace App\Core\Facades;

use App\Core\Application\Cards\Manager;
use Illuminate\Support\Facades\Facade;

class Cards extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return Manager::class;
    }
}
