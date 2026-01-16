<?php

namespace App\Policies;

use App\Models\{User, Project};
use Illuminate\Auth\Access\Response;
use Spatie\Permission\Models\Role;

class ProjectPolicy
{
    public function viewProject(User $user, Project $project){
        
        if($user->id === $project->user_id){
            return Response::allow();
        }

        if($project->users()->where('user_id', $user->id)->exists()){
            return Response::allow();
        }

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
}
