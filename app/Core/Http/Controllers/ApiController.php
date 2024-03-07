<?php

namespace App\Core\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Request;

class ApiController extends Controller
{
    /**
     * General API Response
     *
     * @see  \Illuminate\Contracts\Routing\ResponseFactory
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function response($data = [], $status = 200, array $headers = [], $options = 0)
    {
        // https://stackoverflow.com/questions/57604784/laravel-resource-collection-paginate-json-response-error - for paginated collections
        if ($data instanceof AnonymousResourceCollection) {
            $data = $data->toResponse(Request::instance())->getData();
        }

        return response()->json($data, $status, $headers, $options);
    }
}
