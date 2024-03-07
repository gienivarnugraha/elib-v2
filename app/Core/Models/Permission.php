<?php

namespace App\Core\Models;

use Spatie\Permission\Models\Permission as SpatiePermission;

class Permission extends SpatiePermission
{
    /**
     * Permissions guard name
     *
     * @var string
     */
    public $guard_name = 'api';
}
