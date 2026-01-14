<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;

use App\enums\TaskStatus;
use App\Models\Task;

class TaskPolicy
{
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
