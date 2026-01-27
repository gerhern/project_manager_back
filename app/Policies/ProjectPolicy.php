<?php

namespace App\Policies;

use App\Models\{Membership, User, Project};
use Illuminate\Auth\Access\Response;
use Spatie\Permission\Models\Role;

class ProjectPolicy
{
    /**
     * User only can see the project if user is owner, team admin or user is linked to project
     * @param User $user
     * @param Project $project
     * @return Response
     */
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

        return Response::deny("This action is unauthorized, PPVP", 403);
    }

    /**
     * Only manager can update project data
     * @param User $user
     * @param Project $project
     * @return Response
     */
    public function updateProject(User $user, Project $project): Response {

        $hasRole = $user->hasProjectRole($project, 'Manager');
        
        return $hasRole ? Response::allow() : Response::deny("This action is unauthorized, PPUP", 403); 
    }

    /**
     * Only owner or project manager can try to update project's status to cancel
     * @param User $user
     * @param Project $project
     * @return Response
     */
    public function cancelProject(User $user, Project $project): Response {
        if($user->id === $project->user_id){
            return Response::allow();
        }

        $hasRole = $user->hasProjectRole($project, 'Manager');
        
            return $hasRole ? Response::allow() : Response::deny('This action is unauthorized, PPCP', 403);
    }

    public function createObjective(User $user, Project $project){
    
        if($user-> id === $project->user_id){
            return Response::allow();
        }

        $isValidUser = $user->hasProjectRole($project, ['Manager', 'User']);
        
        return $isValidUser ? Response::allow() : Response::deny('This action is unauthorized, PPCO');
    }
}
