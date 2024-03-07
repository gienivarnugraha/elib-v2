<?php

namespace App\Core\Http\Request;

use App\Core\Application\Table\Table;
use App\Core\Contracts\Resources\Tableable;

class ResourceTableRequest extends ResourceRequest
{
    /**
     * Get the class of the resource being requested.
     *
     * @return \App\Core\Resources\Resource
     */
    public function resource()
    {
        return tap(parent::resource(), function ($resource) {
            abort_if(! $resource instanceof Tableable, 404);
        });
    }

    /**
     * Resolve the resource table for the current request
     */
    public function resolveTable(): Table
    {
        return $this->resource()->resolveTable($this);
    }

    /**
     * Resolve the resource trashed table for the current request
     */
    public function resolveTrashedTable(): Table
    {
        return $this->resource()->resolveTrashedTable($this);
    }
}
