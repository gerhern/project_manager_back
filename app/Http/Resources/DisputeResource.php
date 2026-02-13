<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DisputeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'expired_at' => $this->expired_at,
            'status' => $this->status,
            'project' => new ProjectResource($this->whenLoaded('project')),
            'user'  => new UserResource($this->whenLoaded('user'))
        ];
    }
}
