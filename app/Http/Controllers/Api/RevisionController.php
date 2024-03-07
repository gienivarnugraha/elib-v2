<?php

namespace App\Http\Controllers\Api;

use App\Models\Media;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Core\Facades\Application;
use Illuminate\Support\Facades\Auth;
use App\Core\Application\Date\Carbon;
use App\Http\Resources\MediaResource;
use Illuminate\Database\Query\Builder;
use App\Http\Resources\RevisionResource;
use Illuminate\Support\Facades\Validator;
use App\Core\Http\Controllers\ApiController;
use App\Contracts\Repositories\RevisionRepository;

class RevisionController extends ApiController
{
    /**
     * Initialize new DealStatusController instance
     */
    public function __construct(protected RevisionRepository $repository)
    {
    }

    public function get($type, $id, Request $request)
    {
        $model = Application::resourceByName($type)->repository()->find($id);
        $model->load('revisions.media');
        // $model->with('revisions.user');
        // $model->revisions()->with(['media', 'user']);

        return RevisionResource::collection($model->revisions);
    }
    public function close($id, Request $request)
    {
        $revision = $this->repository->find($id);

        $revision->is_closed = $request->is_closed;
        $revision->save();

        return new RevisionResource($revision);
    }

    public function cancel($id, Request $request)
    {
        $revision = $this->repository->find($id);
        $revision->is_canceled = $request->is_canceled;
        $revision->save();

        return new RevisionResource($revision);
    }

    public function upload($id, Request $request)
    {
        $request->validate([
            'files' => ['required', 'file', 'mimes:pdf'],
        ]);

        $model = $this->repository->upload($id, $request);

        return new MediaResource($model);
    }
}
