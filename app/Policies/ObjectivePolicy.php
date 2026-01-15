<?php

namespace App\Policies;

use App\Models\User;

class ObjectivePolicy
{
    public function updateObjective(User $user){
        return $user->hasPermissionTo('updateObjective');
    }
}
