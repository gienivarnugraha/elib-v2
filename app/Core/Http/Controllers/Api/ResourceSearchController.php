<?php

namespace App\Core\Http\Controllers\Api;

use App\Core\Criteria\RequestCriteria;
use App\Core\Http\Controllers\ApiController;
use App\Core\Http\Request\ResourceRequest;

class ResourceSearchController extends ApiController
{
    /**
     * Perform search for a resource.
     *
     * @param  \App\Innoclapps\Resources\Http\ResourceRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function handle(ResourceRequest $request)
    {
        $resource = tap($request->resource(), function ($resource) {
            abort_if(!$resource::searchable(), 404);
        });

        if (empty($request->q)) {
            return $this->response([]);
        }

        $repository = $resource::repository()
            ->pushCriteria(RequestCriteria::class);

        if ($ownCriteria = $resource->ownCriteria()) {
            $repository->pushCriteria($ownCriteria);
        }

        [$with, $withCount] = $resource::getEagerloadableRelations($resource->resolveFields());

        $repository->withCount($withCount->all())
            ->with($with->all());

        return $this->response(
            $request->toResponse(
                $repository->orderBy($resource::$orderBy, $resource::$orderByDir)->all()
            )
        );
    }
}
