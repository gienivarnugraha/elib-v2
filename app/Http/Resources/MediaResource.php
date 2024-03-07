<?php

namespace App\Http\Resources;

use App\Core\Http\Resources\JsonResource;
use Illuminate\Http\Request;

class MediaResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'name' => $this->name,
            'mime_type' => $this->mime_type,
            'size' => $this->human_readable_size,
            'created_at' => $this->created_at,
            'url' => $this->original_url,
            'preview_url' => $this->preview_url,
        ];
    }
}
