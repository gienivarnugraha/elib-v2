<?php

namespace App\Core\Contracts;

interface Localizeable
{
    public function getLocalTimeFormat();

    public function getLocalDateFormat();

    public function getUserTimezone();
}
