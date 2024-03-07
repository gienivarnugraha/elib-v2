<?php

namespace App\Http\Resources;

use App\Core\Http\Resources\JsonResource;
use Illuminate\Http\Request;

class ManualResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return $this->withCommonData(parent::toArray($request));
    }
}
