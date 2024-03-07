<?php

namespace App\Core\Models;

use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    /**
     * Permissions guard name
     *
     * @var string
     */
    public $guard_name = 'api';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'guard_name',
    ];
}
