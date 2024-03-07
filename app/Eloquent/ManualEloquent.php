<?php

namespace App\Eloquent;

use App\Models\Manual;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use App\Core\Application\Date\Carbon;
use App\Core\Repository\AppRepository;
use App\Contracts\Repositories\ManualRepository;

class ManualEloquent extends AppRepository implements ManualRepository
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
        return Manual::class;
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
     * Save a new entity in repository
     *
     *
     * @return mixed
     */
    public function create(array $data)
    {
        /*
        $index = Arr::pull($data, 'index');
        $indexDate = Arr::pull($data, 'index_date');
        */

        $manual = parent::create($data);

        /*  $manual->revisions()->create([
            'user_id' => Auth::id(),
            'index' => $index,
            'index_date' => $indexDate,
        ]); */

        return $manual;
    }
    /**
     * Save a new entity in repository
     *
     *
     * @return mixed
     */
    public function update(array $data, $id)
    {
        $manual = $this->model->find($id);

        if (Arr::exists($data, 'index') || Arr::exists($data, 'index_date')) {

            $index = Arr::pull($data, 'index');
            $indexDate = Arr::pull($data, 'index_date');

            $manual->revisions()->create([
                'user_id' => Auth::id(),
                'index' => $index,
                'index_date' => $indexDate ?? Carbon::now(),
                'title' => $data['title'],
                'body' => $data['body'],
            ]);
        }

        $manual = parent::update($data, $id);


        return $manual;
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
}
