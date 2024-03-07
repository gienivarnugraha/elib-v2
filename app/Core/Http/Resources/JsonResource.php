<?php

namespace App\Core\Http\Resources;

use App\Core\Facades\Format;
use Illuminate\Http\Request;
use App\Core\Contracts\Presentable;
use App\Core\Traits\ProvidesModelAuthorizations;
use Illuminate\Http\Resources\Json\JsonResource as BaseJsonResource;

class JsonResource extends BaseJsonResource
{
    use ProvidesModelAuthorizations;

    /**
     * Provide common data for the resource
     *
     * @param  array  $data
     * @param  \App\Core\Resources\Http\ResourceRequest  $request
     * @return array
     */
    protected function withCommonData($data = [])
    {
        array_unshift($data, $this->merge([
            'id' => $this->getKey(),
        ]));

        $data[] = $this->mergeWhen($authorizations = $this->getAuthorizations($this->resource), [
            'authorizations' => $authorizations,
        ]);

        // $data[] = $this->merge([
        //     'was_recently_created' => $this->resource->wasRecentlyCreated,
        // ]);

        $data[] = $this->mergeWhen($this->resource instanceof Presentable, [
            'display_name' => $this->display_name,
            'path' => $this->path,
        ]);

        $data['created_at'] = Format::dateTime($this->created_at);
        $data['updated_at'] = Format::dateTime($this->updated_at);

        return $data;
    }
}
