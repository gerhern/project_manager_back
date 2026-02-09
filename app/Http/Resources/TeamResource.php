<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TeamResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'   => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'status' => $this->status->name,
            'role'  => $this->whenPivotLoaded('memberships', function () {
            return match($this->pivot->role_id) {
                1 => 'Owner',
                2 => 'Admin',
                3 => 'Member',
                default => 'Unknown',
            };
        }),
        ];
    }
}
