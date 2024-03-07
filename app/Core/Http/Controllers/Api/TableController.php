<?php

namespace App\Core\Http\Controllers\Api;

use App\Core\Http\Controllers\ApiController;
use App\Core\Http\Request\ResourceTableRequest;
use App\Core\Http\Resources\TableResource;
use Illuminate\Http\JsonResponse;

class TableController extends ApiController
{
    /**
     * Display a table listing of the resource
     *
     * @param  \App\Core\Http\Requests\ResourceTableRequest  $request
     */
    public function index(ResourceTableRequest $request): JsonResponse
    {

        try {
            return $this->response(
                TableResource::collection($request->boolean('trashed') ?
                    $request->resolveTrashedTable()->make() :
                    $request->resolveTable()->make())
            );
        } catch (QueryBuilderException $e) {
            abort(400, $e->getMessage());
        }
    }

    /**
     * Get the resource table settings
     *
     * @param  \App\Http\Requests\ResourceTableRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function settings(ResourceTableRequest $request)
    {
        return $this->response(
            $request->boolean('trashed') ?
              $request->resolveTrashedTable()->settings() :
              $request->resolveTable()->settings()
        );
    }
}
