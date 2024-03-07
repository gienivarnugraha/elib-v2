<?php

namespace App\Core\Facades;

use App\Core\Application\Date\Timezone as BaseTimezone;
use Illuminate\Support\Facades\Facade;

class Timezone extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return BaseTimezone::class;
    }
}
