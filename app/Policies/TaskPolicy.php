<?php

namespace App\Policies;

use App\Models\Objective;
use App\Models\User;
use Illuminate\Auth\Access\Response;

use App\enums\TaskStatus;
use App\Models\Task;

class TaskPolicy
{
    public function viewTasks($user, $project, $objective): Response{
        if($objective->project_id !== $project->id){
            return Response::deny('This action is unauthorized, TKPITK');
        }

        if($user->id === $project->user_id){
            return Response::allow();
        }

        $hasRole = $user->hasProjectRole($project, ['Manager', 'User', 'Viewer']);
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

    public function cancelTask(User $user): Response{
        return $user->hasPermissionTo('cancel_task')
                ? Response::allow()
                : Response::deny("Solo un gerente puede cancelar tareas.");
    }

    public function updateStatus(User $user, Task $task, string $status): Response{

        if($user->hasPermissionTo('updateStatus'))
            return Response::allow();

        if ($user->hasPermissionTo('completeTask') && $status === TaskStatus::Completed->name){
            return $user->id === $task->user_id
                ? Response::allow()
                : Response::deny("Solo el asignado a la tarea puede marcarla como completada.");
        }
        
        return Response::deny(  "No tienes permiso para actualizar el estado de la tarea.");
    }
}
