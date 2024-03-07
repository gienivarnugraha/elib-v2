<?php

namespace App\Core\Http\Request;

use App\Core\Facades\Application;
use Illuminate\Foundation\Http\FormRequest;

class ResourceRequest extends FormRequest
{
    /**
     * Resource for the request
     *
     * @var \App\Core\Resources\Resource
     */
    protected $resource;

    /**
     * The request resource record
     *
     * @var \Illuminate\Database\Eloquent\Model
     */
    protected $record;

    /**
     * Get the resource name for the current request
     *
     * @return string
     */
    public function resourceName()
    {
        return $this->route('resource');
    }

    /**
     * Get the request resource id
     *
     * @return int
     */
    public function resourceId()
    {
        return $this->route('resourceId');
    }

    /**
     * Get the class of the resource being requested.
     *
     * @return \App\Core\Resources\Resource
     */
    public function resource()
    {
        if ($this->resource) {
            return $this->resource;
        }


        return $this->resource = tap(
            $this->findResource($this->resourceName()),
            function ($resource) {
                abort_if(is_null($resource), 404);
            }
        );
    }

    /**
     * Get the resource record for the current request
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function record()
    {
        if (! $this->record) {
            $this->record = $this->resource()->repository()->find($this->resourceId());
        }

        return $this->record;
    }

    /**
     * Get resource by a given name
     *
     * @param  string  $name
     * @return \App\Core\Resources\Resource|null
     */
    public function findResource($name)
    {
        if (! $name) {
            return null;
        }

        return Application::resourceByName($name);
    }

    /**
     * Resolve the resource json resource and create appropriate response
     *
     * @param  mixed  $data
     * @return array
     */
    public function toResponse($data)
    {
        if (! $this->resource()->jsonResource()) {
            return $data;
        }

        return $this->resource()->createJsonResource($data);
    }

    /**
     * Check whether the current request is for create
     *
     * @return bool
     */
    public function isCreateRequest()
    {
        return $this instanceof CreateResourceRequest;
    }

    /**
     * Check whether the current request is for update
     *
     * @return bool
     */
    public function isUpdateRequest()
    {
        return $this instanceof UpdateResourceRequest;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            //
        ];
    }
}
