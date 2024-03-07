<?php

namespace App\Core\Http\Controllers\Api;

use App\Core\Facades\Fields;
use App\Core\Http\Controllers\ApiController;
use App\Core\Http\Request\ResourceRequest;

class FieldController extends ApiController
{
    /**
     * Get the resource create fields
     *
     * @param  \App\Http\Requests\ResourceRequest  $request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function create(ResourceRequest $request)
    {
        return $this->response(
            Fields::resolveCreateFieldsForDisplay($request->resourceName())
        );
    }

    /**
     * Get the resource update fields
     *
     * @param  \App\Http\Requests\ResourceRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(ResourceRequest $request)
    {
        $request->resource()->setModel($request->record());

        return $this->response(
            Fields::resolveUpdateFieldsForDisplay($request->resourceName())
        );
    }
}
