<?php

namespace App\Eloquent;

use App\Models\Revision;
use App\Http\Resources\MediaResource;
use App\Core\Repository\AppRepository;
use App\Contracts\Repositories\RevisionRepository;
use Illuminate\Http\Request;

class RevisionEloquent extends AppRepository implements RevisionRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public static function model()
    {
        return Revision::class;
    }

    /**
     * Boot the repository
     *
     * @return void
     */
    public static function boot()
    {
    }

    public function upload($id, Request $request)
    {
        $model = $this->find($id);

        $resource = class_basename($model->revisable_type);
        $file = $request->file('files');

        if (in_array($resource, ['Document', 'Manual'])) { // 'manuals',

            if ($resource === 'Document') {
                $name = $model->revisable->no . '_' . $model->index;
            }

            if ($resource === 'Manual') {
                $name = $model->revisable->part_number . '_' . $model->index;
            }

            $filename = $name . '.' . $file->extension();

            $exist = $model->media()->where('name', $name)->get();

            if (count($exist) > 0) {
                $model->media()->delete($exist);
            }

            $model
                ->addMediaFromRequest('files')
                ->usingName($name)
                ->usingFileName($filename)
                ->toMediaCollection($resource);

            return $model->getMedia($resource)->last();
        } else {
            abort(404, `Resource is not mediable`);
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
            // 'media',
            // 'user',
        ];
    }
}
