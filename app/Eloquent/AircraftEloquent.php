<?php

namespace App\Eloquent;

use App\Contracts\Repositories\AircraftRepository;
use App\Core\Repository\AppRepository;
use App\Models\Aircraft;

class AircraftEloquent extends AppRepository implements AircraftRepository
{
    /**
     * Searchable fields
     *
     * @var array
     */
    protected static $fieldSearchable = [
        'type' => 'like',
        'serial_num' => 'like',
        'reg_code' => 'like',
        'effectivity' => 'like',
        'owner' => 'like',
        'manuf_date' => 'like',
    ];

    /**
     * Specify Model class name
     *
     * @return string
     */
    public static function model()
    {
        return Aircraft::class;
    }

    /**
     * Boot the repository
     *
     * @return void
     */
    public static function boot()
    {
    }

    /**
     * The relations that are required for the response
     *
     * @return array
     */
    protected function eagerLoad()
    {
        return [
            'documents',
            'manuals'
        ];
    }
}
