<?php

namespace App\Policies;

use App\Models\{User, Project};
use Illuminate\Auth\Access\Response;
use Spatie\Permission\Models\Role;

class ProjectPolicy
{
    public function viewProject(User $user, Project $project){
        
        //Owner can see their own project
        if($user->id === $project->user_id){
            return Response::allow();
        }

        //Every projects member can see it
        if($project->users()->where('user_id', $user->id)->exists()){
            return Response::allow();
        }

        //Team admin can see project
        $roleId = Role::where('name', 'Admin')->first()->id;
        $isTeamAdmin = $project
            ->team
            ->members()
            ->where('user_id', $user->id)
            ->wherePivot('role_id', $roleId)
            ->exists();

        if($isTeamAdmin){
            return Response::allow();
        }

        return Response::deny("You can't access");
    }

    public function updateProject(User $user, Project $project): Response {
        $managerRole = Role::where('name', 'Manager')->first();
        $permission = $user->projects()
            ->where('model_id', $project->id)
            ->wherePivot('role_id',$managerRole->id)
            ->exists();
        
        return $permission ? Response::allow() : Response::deny("Operation denied", 403); 
    }
}
