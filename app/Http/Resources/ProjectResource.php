<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'name'        => $this->name,
            'description' => $this->description,
            'status'      => $this->status,
            // 'team'        => new TeamResource($this->whenLoaded('team')),
            // 'owner'       => new UserResource($this->whenLoaded('creator'))
            'objectives'  => ObjectiveResource::collection($this->whenLoaded('objectives')),
        ];  
    }
}
