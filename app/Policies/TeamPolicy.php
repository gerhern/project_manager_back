<?php

namespace App\Policies;

use App\Models\Membership;
use App\Models\Team;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Auth\Access\Response;

class TeamPolicy
{

    /**
     * User has permission to update team?
     * @param User $user
     * @return bool
     */
    public function updateTeam(User $user, Team $team): Response
    {
        $hasRole = Membership::where('user_id', $user->id)
        ->where('model_id', $team->id)
        ->where('model_type', Team::class)
        ->whereHas('role', function($q) {
            $q->whereIn('name', ['Admin', 'Owner']);
        })
        ->exists();

        return $hasRole ? Response::allow() : Response::deny('This action is unauthorized, TPUT');
    }

    /**
     * User has permission to change team's status to Inactive?
     * @param User $user
     * @param Team $team
     * @return bool
     */
    public function inactiveTeam(User $user, Team $team): Response {    


        $hasRole = Membership::where('user_id', $user->id)
            ->where('model_id', $team->id)
            ->where('model_type', Team::class)
            ->whereHas('role', function($q) {
                $q->whereIn('name', ['Admin', 'Owner']);
            })
            ->exists();

        return $hasRole ? Response::allow() : Response::deny('This action is unauthorized, TPIT');

    }

    /**
     * User has permission to create new project on the current team?
     * @param User $user
     * @param Team $team
     * @return void
     */
    public function createProject(User $user, Team $team): Response {

        $hasRoles = Membership::where('user_id', $user->id)
        ->where('model_id', $team->id)
        ->where('model_type', Team::class)
        ->whereHas('role', function($q) {
            $q->whereIn('name', ['Admin', 'Owner']);
        })
        ->exists();

        return $hasRoles ? Response::allow() : Response::deny('This action is unauthorized, TPCP', 403); 
    }
}
