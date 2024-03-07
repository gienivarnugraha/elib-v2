<?php

namespace App\Core\Http\Controllers\Api;

use App\Core\Http\Controllers\ApiController;
use App\Core\Http\Request\CreateResourceRequest;
use App\Core\Http\Request\ResourcefulRequest;
use App\Core\Http\Request\UpdateResourceRequest;
use Illuminate\Http\JsonResponse;

class ResourcefulController extends ApiController
{
    /**
     * Display a listing of the resource.
     *
     * @param  \App\Core\Resources\Http\ResourcefulRequest  $request
     */
    public function index(ResourcefulRequest $request): JsonResponse
    {
        // Resourceful index flag
        $this->authorize('viewAny', $request->resource()->model());

        return $this->response(
            $request->toResponse(
                $request->resource()->resourcefulHandler($request)->index()
            )
        );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Core\Resources\Http\CreateResourceRequest  $request
     */
    public function store(CreateResourceRequest $request): JsonResponse
    {
        // Resourceful store flag
        $this->authorize('create', $request->resource()->model());

        $record = $request->resource()->displayQuery()->find(
            $request->resource()->resourcefulHandler($request)->store()->getKey()
        );

        // Set that this record was recently created as the property value is lost
        // because we are re-querying the record again after creation
        $record->wasRecentlyCreated = true;

        return $this->response(
            $request->toResponse($record),
            201
        );
    }

    /**
     * Display resource record.
     *
     * @param  \App\Core\Resources\Http\ResourcefulRequest  $request
     */
    public function show(ResourcefulRequest $request): JsonResponse
    {
        // Resourceful show flag
        $this->authorize('view', $request->record());

        return $this->response(
            $request->toResponse(
                $request->resource()->resourcefulHandler($request)->show($request->resourceId())
            )
        );
    }

    /**
     * Update resource record in storage.
     *
     * @param  \App\Core\Resources\Http\UpdateResourceRequest  $request
     */
    public function update(UpdateResourceRequest $request): JsonResponse
    {
        // Resourceful update flag
        $this->authorize('update', $request->record());

        $request->resource()->resourcefulHandler($request)->update($request->resourceId());

        $record = $request->resource()->displayQuery()->find($request->resourceId());

        return $this->response(
            $request->toResponse($record)
        );
    }

    /**
     * Remove resource record from storage.
     *
     * @param  \App\Core\Resources\Http\ResourcefulRequest  $request
     */
    public function destroy(ResourcefulRequest $request): JsonResponse
    {
        // Resourceful destroy flag
        $this->authorize('delete', $request->record());

        $content = $request->resource()->resourcefulHandler($request)->destroy($request->resourceId());

        return $this->response($content, empty($content) ? 204 : 200);
    }
}
