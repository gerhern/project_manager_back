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

        $isValidUser = $user->hasProjectRole($project, ['Manager', 'User']);
        

        return $isValidUser ? Response::allow() : Response::deny('This action is unauthorized, OPUO');
    }

    public function cancelObjective(User $user, $objective, $project): Response{

        if($user->id === $project->user_id){
            return Response::allow();
        }

        $isValidUser = $user->hasProjectRole($project, ['Manager']);
        return $isValidUser ? Response::allow() : Response::deny('This action is unauthorized, OPCO');
    }
    
}
