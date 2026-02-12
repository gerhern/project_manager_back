<?php

namespace App\Policies;

use App\Enums\RoleList;
use App\Models\Membership;
use App\Models\Objective;
use App\Models\Project;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ObjectivePolicy
{

    public function indexObjective(User $user, Project $project): Response {
        if($user->id === $project->user_id){
            return Response::allow();
        }
        $isValidUser = $user->hasProjectRole($project, RoleList::projectRoles());
        return $isValidUser ? Response::allow() : Response::deny('This action is unauthorized, OPIO');
    }

    public function createObjective(User $user, Project $project): Response {
        if($user->id === $project->user_id){
            return Response::allow();
        }
        $isValidUser = $user->hasProjectRole($project, [RoleList::Manager, RoleList::User]);
        
        return $isValidUser ? Response::allow() : Response::deny('This action is unauthorized, OPCO');
    }

    public function viewObjective(User $user, Project $project, Objective $objective){
        if($project->id !== $objective->project_id){
            return Response::deny('This action is unauthorized, OPVO');
        }
        if($user->id === $project->user_id || $user->id === $objective->user_id){
            return Response::allow();
        }
        $isValidUser = $user->hasProjectRole($project, RoleList::projectRoles());
        

        return $isValidUser ? Response::allow() : Response::deny('This action is unauthorized, OPVO');
    }
    public function updateObjective(User $user, $objective, $project){

        if($project->id !== $objective->project_id){
            return Response::deny('This action is unauthorized, OPUO');
        }

        if($user->id === $project->user_id || $user->id === $objective->user_id){
            return Response::allow();
        }

        $isValidUser = $user->hasProjectRole($project, [RoleList::Manager, RoleList::User]);
        

        return $isValidUser ? Response::allow() : Response::deny('This action is unauthorized, OPUO');
    }

    public function cancelObjective(User $user, $objective, $project): Response{

        if($user->id === $project->user_id){
            return Response::allow();
        }

        $isValidUser = $user->hasProjectRole($project, RoleList::Manager);
        return $isValidUser ? Response::allow() : Response::deny('This action is unauthorized, OPCO');
    }
    
}
