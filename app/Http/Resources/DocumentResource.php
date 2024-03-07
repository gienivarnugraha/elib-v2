<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use App\Http\Resources\UserResource;
use App\Core\Http\Resources\JsonResource;

class DocumentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return $this->withCommonData(
            array_merge(
                [
                    $this->mergeWhen($this->whenLoaded('revisions'), [
                        'index' => $this->revisions()->orderBy('id', 'desc')->latest()->first()->index,
                    ]),
                ],

                parent::toArray($request)
            )
        );
    }
}
