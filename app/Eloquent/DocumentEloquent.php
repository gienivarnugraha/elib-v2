<?php

namespace App\Eloquent;

use App\Models\Document;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use App\Core\Application\Date\Carbon;
use App\Core\Repository\AppRepository;
use App\Contracts\Repositories\AircraftRepository;
use App\Contracts\Repositories\DocumentRepository;

class DocumentEloquent extends AppRepository implements DocumentRepository
{
    /**
     * Searchable fields
     *
     * @var array
     */
    protected static $fieldSearchable = [
        'no' => 'like',
        'office' => 'like',
        'type' => 'like',
        'subject' => 'like',
        'reference' => 'like',
    ];

    /**
     * Specify Model class name
     *
     * @return string
     */
    public static function model()
    {
        return Document::class;
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
        $aircraft = resolve(AircraftRepository::class)->find($data['aircraft_id'])->type;
        $data['no'] = $this->model->generateNumber($data['type'], $data['office'], $aircraft);

        $document = parent::create($data);

        $index = $data['office'] === 'DOA' ? 'A' : 'NE';

        $document->revisions()->create([
            'user_id' => Auth::id(),
            'index' => $index,
            'title' => 'New Document',
            'body' => 'New Document',
            'index_date' => Carbon::now(),
        ]);

        return $document;
    }

    /**
     * Update a new entity in repository
     *
     *
     * @return mixed
     */
    public function update(array $data, $id)
    {

        // ! used if type office aircraft_id can be changed
        //    $document = $this->model->find($id);
        // if (isset($data['type']) || isset($data['office']) || isset($data['aircraft_id'])) {
        //     $aircraft = isset($data['aircraft_id']) ? resolve(AircraftRepository::class)->find($data['aircraft_id'])->type : $model->aircraft->type;
        //     $type = isset($data['type']) ? $data['type'] : $model->type;
        //     $office = isset($data['office']) ? $data['office'] : $model->office;
        //     $data['no'] = $this->model->generateNumber($type, $office, $aircraft);
        // }

        // $document = parent::update($data, $id);

        $document = $this->model->find($id);

        if (Arr::exists($data, 'title')) {

            $revNumber = $this->generateRevisionNumber($document);

            $document->revisions()->create([
                'user_id' => Auth::id(),
                'title' => $data['title'],
                'body' => $data['body'],
                'index' => $revNumber,
                'index_date' => Carbon::now(),
            ]);
        }

        $document = parent::update($data, $id);


        return $document;
    }

    public function getLatestRevisions(Document $document)
    {
        return $document->revisions()->orderBy('id', 'desc')->latest()->first();
    }

    public function generateRevisionNumber(Document $document)
    {
        $latestRev = $this->getLatestRevisions($document);

        $office = $document->office->value;

        if ($latestRev->is_closed === true || $latestRev->is_canceled === true) {
            if ($office === 'AMO') {
                if ($latestRev->index === 'NE') {
                    return 'R1';
                } else {
                    $getLastIndex = (int) ltrim($latestRev->index, 'R');

                    return 'R' . $getLastIndex + 1;
                }
            }
            if ($office === 'DOA') {
                $index = $latestRev->index;
                $index++;

                return $index;
            }
        } else {
            abort(409, 'Close the latest revision first!');
        }
    }

    /**
     * The relations that are required for the response
     *
     * @return array
     */
    protected function eagerLoad()
    {
        return [
            //'revisions',
        ];
    }
}
