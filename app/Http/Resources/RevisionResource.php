<?php

namespace App\Http\Resources;

use App\Core\Facades\Format;
use Illuminate\Http\Request;
use App\Http\Resources\MediaResource;
use App\Core\Http\Resources\JsonResource;

class RevisionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return $this->withCommonData(
            [
                $this->mergeWhen($this->whenLoaded('user'), [
                    'user' => new UserResource($this->user),
                ]),
                $this->mergeWhen($this->whenLoaded('media'), [
                    'media' => MediaResource::collection($this->media)->first(),
                ]),
                'created_at' => Format::dateTime($this->created_at),
                'index' => $this->index,
                'body' => $this->body,
                'title' => $this->title,
                'is_closed' => $this->is_closed,
                'is_canceled' => $this->is_canceled,
                'index_date' => Format::date($this->created_at),
            ],
        );
    }
}
