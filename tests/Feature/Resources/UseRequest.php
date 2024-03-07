<?php

namespace Tests\Feature\Resources;

use App\Core\Http\Request\CreateResourceRequest;
use App\Core\Http\Request\ResourcefulRequest;
use App\Core\Http\Request\ResourceTableRequest;
use App\Core\Http\Request\UpdateResourceRequest;
use App\Models\User;
use Illuminate\Support\Facades\Route;

/* This trait is used to mock request and debug the resource without performing api request from user */

trait UseRequest
{
    protected function tableRequest($params = null)
    {
        $query = (is_array($params) ? http_build_query($params) : $params);

        $request = ResourceTableRequest::create($this->tableEndpoint().$query, 'GET');

        return $this->setRouteResolver($request);
    }

    protected function updateRequest($record, $data)
    {
        $request = UpdateResourceRequest::create($this->updateEndpoint($record), 'PUT', $data);

        return $this->setRouteResolver($request);
    }

    protected function createRequest($data)
    {
        $request = CreateResourceRequest::create($this->createEndpoint(), 'POST', $data);

        return $this->setRouteResolver($request);
    }

    protected function request($params = null)
    {
        $query = is_null($params) ? '' : '?'.(is_array($params) ? http_build_query($params) : $params);
        $request = ResourcefulRequest::create($this->endpoint().$query, 'GET');

        return $this->setRouteResolver($request);
    }

    protected function setRouteResolver($request)
    {
        $user = User::find(1) ?: $this->createUser();

        $request->setUserResolver(fn () => $user);

        $request->setRouteResolver(function () use ($request) {
            $routes = Route::getRoutes();

            return $routes->match($request);
        });

        return $request;
    }
}
