<?php

namespace App\Policies;

use App\Enums\DisputeStatus;
use App\Enums\ProjectStatus;
use App\Models\ProjectDispute;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ProjectDisputePolicy
{

    public function updateDisputeStatus(User $user, ProjectDispute $dispute): Response{
         $isResolved = in_array($dispute->status, DisputeStatus::resolutionStates());
         $projectIsCanceled = in_array($dispute->project->status, ProjectStatus::completedStates());

         if($isResolved){
            return Response::deny('This dispute has already been resolved, PPDUDS');
         }

         if($projectIsCanceled){
            return Response::deny('This project is inactive, PPDUDS');
         }

        $ownerId = $dispute->project->user_id;

        return $user->id === $ownerId
                ? Response::allow()
                : Response::deny('This action is unauthorized, PPDUDS');

    }
}
