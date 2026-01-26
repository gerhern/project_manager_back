<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ObjectivePolicy
{
    public function updateObjective(User $user){
        return $user->hasPermissionTo('updateObjective');
    }
}
