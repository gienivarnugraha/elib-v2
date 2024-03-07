<?php

namespace App\Core\Contracts;

use Illuminate\Database\Eloquent\Casts\Attribute;

interface Presentable
{
    public function displayName(): Attribute;

    public function path(): Attribute;

    public function getKeyName();

    public function getKey();
}
