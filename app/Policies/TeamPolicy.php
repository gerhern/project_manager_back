<?php

namespace App\Policies;

use App\Models\Team;
use App\Models\User;
use Spatie\Permission\Models\Role;

class TeamPolicy
{

    public function updateTeam(User $user)
    {
        return $user->hasPermissionTo('updateTeam');
    }

    public function inactiveTeam(User $user, Team $team): bool {    
        return $user->teams()->where('model_id', $team->id)
        ->wherePivot('role_id', Role::where('name', 'Owner')->value('id'))
        ->exists();

    }
}
