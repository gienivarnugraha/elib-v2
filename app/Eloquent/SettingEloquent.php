<?php

namespace App\Eloquent;

use App\Contracts\Repositories\SettingRepository;
use App\Core\Repository\AppRepository;
use App\Models\Setting;

class SettingEloquent extends AppRepository implements SettingRepository
{
    /**
     * Searchable fields
     *
     * @var array
     */
    protected static $fieldSearchable = [];

    /**
     * Specify Model class name
     *
     * @return string
     */
    public static function model()
    {
        return Setting::class;
    }
}
