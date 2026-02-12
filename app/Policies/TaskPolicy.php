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
        if($objective->project_id !== $objective->project->id){
            return Response::deny('This action is unauthorized, TKPITK');
        }

        if($user->id === $objective->project->user_id){
            return Response::allow();
        }

        $hasRole = $user->hasProjectRole($objective->project, RoleList::projectRoles());
        return $hasRole ? Response::allow() : Response::deny('This action is unauthorized, TKPITK');
    }

    public function createTask($user, $project, $objective): Response {
        if($objective->project_id !== $project->id){
            return Response::deny('This action is unauthorized, TKPCTK');
        }

        if($user->id === $project->user_id){
            return Response::allow();
        }

        $hasRole = $user->hasProjectRole($project, ['Manager', 'User']);
        return $hasRole ? Response::allow() : Response::deny('This action is unauthorized, TKPCTK');
    }

    public function viewTask($user, $project, $objective, $task): Response{
        if($objective->project_id !== $project->id || $task->objective_id !== $objective->id){
            return Response::deny('This action is unauthorized, TKPSTK');
        }

        if($user->id === $project->user_id){
            return Response::allow();
        }

        $hasRole = $user->hasProjectRole($project, ['Manager', 'User', 'Viewer']);
        return $hasRole ? Response::allow() : Response::deny('This action is unauthorized, TKPSTK');
    }

    public function updateTask(User $user, Project $project, Objective $objective, Task $task): Response {
        if($objective->id !== $task->objective_id || $project->id !== $objective->project_id){
            return Response::deny('This action is unauthorized, TKPUTK');
        }

        if($user->id === $project->user_id){
            return Response::allow();
        }

        $hasRole = $user->hasProjectRole($project, ['Manager', 'User']);
        return $hasRole ? Response::allow() : Response::deny('This action is unauthorized, TKPUTK');
    }

    public function cancelTask(User $user, Project $project, Objective $objective, Task $task): Response {
        if($objective->id !== $task->objective_id || $project->id !== $objective->project_id){
            return Response::deny('This action is unauthorized, TKPDTK');
        }

        if($user->id === $project->user_id){
            return Response::allow();
        }

        $hasRole = $user->hasProjectRole($project, ['Manager', 'User']);
        return $hasRole ? Response::allow() : Response::deny('This action is unauthorized, TKPDTK');
    }

    public function updateTaskStatus(User $user, Project $project, Objective $objective, Task $task, string $status = null): Response {
        if (!$status || $status === TaskStatus::Canceled->name){
            return Response::deny('This action is unauthorized, TKPUSTK');
        }

        if($objective->id !== $task->objective_id || $project->id !== $objective->project_id){
            return Response::deny('This action is unauthorized, TKPUSTK');
        }
        
        if($user->id === $project->user_id){
            return Response::allow();
        }

        if($task->user_id !== $user->id){
            return Response::deny('This action is unauthorized, TKPUSTK');
        }

        $hasRole = $user->hasProjectRole($project, ['Manager', 'User']);

        return $hasRole ? Response::allow() : Response::deny('This action is unauthorized, TKPUSTK');
    }
}
