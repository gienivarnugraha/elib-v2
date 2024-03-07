<?php

namespace App\Http\Resources;

use App\Core\Http\Resources\JsonResource;
use Illuminate\Http\Request;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return parent::withCommonData(
            [
                'name' => $this->name,
                'email' => $this->email,
                'nik' => $this->nik,
                'org' => $this->org,
                'avatar' => $this->avatarUrl,
                // 'settings'      => Application::userInformation($this->resource),
                $this->mergeWhen(!request()->method('PUT') || !request()->method('POST'), [
                    'permissions' => $this->getPermissionsViaRoles()->pluck('name'),
                    'permissionsAll' => $this->getAllPermissions()->pluck('name'),
                ]),
                $this->mergeWhen($this->whenLoaded('documents'), [
                    'documents' => DocumentResource::collection($this->whenLoaded('documents')),
                ]),
                $this->mergeWhen($this->whenLoaded('settings'), [
                    'settings' => new SettingResource($this->whenLoaded('settings')),
                ]),
                $this->mergeWhen($this->whenLoaded('revisions'), [
                    'revisions' => RevisionResource::collection($this->whenLoaded('revisions')),
                ]),
                $this->mergeWhen($this->whenLoaded('roles'), [
                    'roles' => RoleResource::collection($this->whenLoaded('roles'))->pluck('name'),
                ]),
            ]
        );
    }
}
