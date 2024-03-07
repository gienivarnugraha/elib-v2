<?php

namespace App\Eloquent;

use App\Core\Repository\AppRepository;
use App\Contracts\Repositories\MediaRepository;
use App\Models\Media;

class MediaEloquent extends AppRepository implements MediaRepository
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
        return Media::class;
    }
}
