<?php

namespace App\Policies;

use App\Models\ProjectDispute;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ProjectDisputePolicy
{

    public function updateDisputeStatus(User $user, ProjectDispute $dispute): Response{
        $ownerId = $dispute->project->user_id;

        return $user->id === $ownerId
                ? Response::allow()
                : Response::deny("Solo el creador del proyecto puede modificar el estatus de esta disputa.");

    }
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, ProjectDispute $projectDispute): bool
    {
        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ProjectDispute $projectDispute): bool
    {
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ProjectDispute $projectDispute): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, ProjectDispute $projectDispute): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, ProjectDispute $projectDispute): bool
    {
        return false;
    }
}
