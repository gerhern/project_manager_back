<?php

namespace App\Policies;

use App\Models\Membership;
use App\Models\Project;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ObjectivePolicy
{
    public function updateObjective(User $user, $objective, $project){
        if($user->id === $project->user_id || $user->id === $objective->user_id){
            return Response::allow();
        }
        
        $isValidUser = Membership::where('user_id', $user->id)
            ->where('model_id', $project->id)
            ->where('model_type', Project::class)
            ->whereHas('role', function($q){
                $q->whereIn('name', ['Manager', 'User']);
            })
            ->exists();

        return $isValidUser ? Response::allow() : Response::deny('This action is unauthorized, OPUO');
    }
}
