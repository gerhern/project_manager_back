<?php

namespace App\Policies;

use App\Models\User;

class TeamPolicy
{

    public function updateTeam(User $user)
    {
        return $user->hasPermissionTo('updateTeam');
    }
}
