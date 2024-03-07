<?php

namespace App\Eloquent;

use App\Models\Media;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Core\Application\Date\Carbon;
use App\Core\Repository\AppRepository;
use App\Contracts\Repositories\OrderRepository;
use App\Http\Resources\OrderResource;

class OrderEloquent extends AppRepository implements OrderRepository
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
        return Order::class;
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
        return [];
    }

    public function create($data)
    {
        $media = Media::findByUuid($data['uuid']);

        $data['media_id'] = $media->id;
        $data['user_id'] = Auth::id();

        $model = parent::create($data);

        return new OrderResource($model);
    }
}
