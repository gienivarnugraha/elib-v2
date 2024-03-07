<?php

namespace App\Core\Macros;

class ToObject
{
    public function __invoke($array)
    {
        if (! is_array($array) && ! is_object($array)) {
            return new \stdClass();
        }

        return json_decode(json_encode((object) $array));
    }
}
