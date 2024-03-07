<?php

namespace App\Core\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TableResource extends JsonResource
{
    /**
     * Transform the table into an array.
     * Uses Json resource create a proper API pagination formatting
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return $this->resource->toArray();
    }
}
