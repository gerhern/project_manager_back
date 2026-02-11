<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Spatie\Permission\Models\Role;

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
            'role'  => $this->getRole(),
        //     'role'  => $this->whenPivotLoaded('memberships', function () {
        //     return match($this->pivot->role_id) {
        //         1 => 'Owner',
        //         2 => 'Admin',
        //         3 => 'Member',
        //         default => 'Unknown',
        //     };
        // }),

        ];
    }

    protected function getRole(){
        if ($this->pivot && $this->pivot instanceof Membership) {
            return $this->pivot->relationLoaded('role') 
                ? $this->pivot->role->role_name 
                : Role::find($this->pivot->role_id)?->role_name;
        }
        return $this->role_name;
    }
}
