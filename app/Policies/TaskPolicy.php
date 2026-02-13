<?php

namespace App\Policies;

use App\Enums\RoleList;
use App\Models\Objective;
use App\Models\Project;
use App\Models\User;
use Illuminate\Auth\Access\Response;

use App\enums\TaskStatus;
use App\Models\Task;

class TaskPolicy
{
    public function viewTasks($user, $objective): Response{
        $project = $objective->project;
        if($user->id === $project->user_id){
            return Response::allow();
        }

        $hasRole = $user->hasProjectRole($project, RoleList::projectRoles());
        return $hasRole ? Response::allow() : Response::deny('This action is unauthorized, TKPITK');
    }

    public function createTask($user, $objective): Response {
        $project = $objective->project;

        if($user->id === $project->user_id){
            return Response::allow();
        }

        $hasRole = $user->hasProjectRole($project, [RoleList::Manager, RoleList::User]);
        return $hasRole ? Response::allow() : Response::deny('This action is unauthorized, TKPCTK');
    }

    public function viewTask($user, $objective, $task): Response{
        $project = $objective->project;
        if($user->id === $project->user_id){
            return Response::allow();
        }

        $hasRole = $user->hasProjectRole($project, RoleList::projectRoles());
        return $hasRole ? Response::allow() : Response::deny('This action is unauthorized, TKPSTK');
    }

    public function updateTask(User $user, Objective $objective, Task $task): Response {
        $project = $objective->project;
        if($user->id === $project->user_id){
            return Response::allow();
        }

        $hasRole = $user->hasProjectRole($project, [RoleList::Manager, RoleList::User]);
        return $hasRole ? Response::allow() : Response::deny('This action is unauthorized, TKPUTK');
    }

    public function cancelTask(User $user, Objective $objective, Task $task): Response {
        $project = $objective->project;
        if($user->id === $project->user_id){
            return Response::allow();
        }

        $hasRole = $user->hasProjectRole($project, [RoleList::Manager, RoleList::User]);
        return $hasRole ? Response::allow() : Response::deny('This action is unauthorized, TKPDTK');
    }

    public function updateTaskStatus(User $user, Objective $objective, Task $task, string $status = null): Response {
        $project = $objective->project;
        if ($status === null || $status === TaskStatus::Canceled->value){
            return Response::deny('This action is unauthorized, TKPUSTK');
        }
        
        if($user->id === $project->user_id || $task->user_id === $user->id){
            return Response::allow();
        }

        $hasRole = $user->hasProjectRole($project, RoleList::Manager);

        return $hasRole ? Response::allow() : Response::deny('This action is unauthorized, TKPUSTK');
    }
}
