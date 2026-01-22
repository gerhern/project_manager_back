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
                : Response::deny('This action is unauthorized, PPDUDS');

    }
}
