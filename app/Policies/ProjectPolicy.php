<?php

namespace App\Policies;

use App\Enums\RoleList;
use App\Models\{Membership, User, Project, Team};
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
    public function viewProject(User $user, Team $team, Project $project)
    {
// dd(RoleList::teamManagementTier());
        //Owner can see their own project
        if ($user->id === $project->user_id) {
            return Response::allow();
        }

        //Every projects member can see it
        if ($project->users()->where('user_id', $user->id)->exists()) {
            return Response::allow();
        }

        //Team admin can see project
        $hasRoles = Membership::where('user_id', $user->id)
        ->where('model_id', $team->id)
        ->where('model_type', Team::class)
        ->whereHas('role', function($q) {
            $q->whereIn('name', RoleList::teamManagementTier());
        })
        ->exists();

        if ($hasRoles) {
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
    public function updateProject(User $user, Team $team,  Project $project): Response
    {
        if ($user->id === $project->user_id) {
            return Response::allow();
        }
        $hasRole = $user->hasProjectRole($project, RoleList::Manager);

        return $hasRole ? Response::allow() : Response::deny("This action is unauthorized, PPUP", 403);
    }

    /**
     * Only owner or project manager can try to update project's status to cancel
     * @param User $user
     * @param Project $project
     * @return Response
     */
    public function cancelProject(User $user, Project $project): Response
    {
        if ($user->id === $project->user_id) {
            return Response::allow();
        }

        $hasRole = $user->hasProjectRole($project, 'Manager');

        return $hasRole ? Response::allow() : Response::deny('This action is unauthorized, PPCP', 403);
    }

    public function createObjective(User $user, Project $project)
    {

        if ($user->id === $project->user_id) {
            return Response::allow();
        }

        $isValidUser = $user->hasProjectRole($project, ['Manager', 'User']);

        return $isValidUser ? Response::allow() : Response::deny('This action is unauthorized, PPCO');
    }
}
