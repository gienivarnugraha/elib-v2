<?php

namespace App\Core\Http\Controllers\Api;

use App\Core\Facades\Application;
use App\Core\Http\Controllers\ApiController;
use App\Core\Resources\GlobalSearch;
use Illuminate\Http\Request;

class GlobalSearchController extends ApiController
{
    /**
     * Perform global search
     *
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function handle(Request $request)
    {
        if (empty($request->q)) {
            return $this->response([]);
        }

        return $this->response(
            new GlobalSearch(
                Application::globallySearchableResources()
            )
        );
    }
}
