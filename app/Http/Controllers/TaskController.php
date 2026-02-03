<?php

namespace App\Http\Controllers;

use App\enums\TaskStatus;
use App\Http\Requests\TaskStoreRequest;
use App\Http\Requests\TaskUpdaterequest;
use App\Models\Objective;
use App\Models\Project;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

use App\Models\Task;

class TaskController extends Controller
{
    use ApiResponse;

    public function index(Request $request, Project $project, Objective $objective): JsonResponse {
        Gate::authorize('viewTasks', [Task::class, $project, $objective]);
        $tasks = $objective->tasks;
        return $this->sendApiResponse($tasks, 'Tasks retrieved successfully');
    }

    public function store(TaskStoreRequest $request, Project $project, Objective $objective): JsonResponse {
        Gate::authorize('createTask', [Task::class, $project, $objective]);

        
        $task = Task::create(
            $request->validated() + [
                'objective_id'  => $objective->id,
                'status'        => $request->filled('user_id') ? TaskStatus::Assigned->name : TaskStatus::Pending
            ]);

        return $this->sendApiResponse($task, 'Task created successfully', 201);
    }

    public function show(Request $request, Project $project, Objective $objective, Task $task): JsonResponse {
        Gate::authorize('viewTask', [Task::class, $project, $objective, $task]);
        return $this->sendApiResponse($task, 'Task retrieved successfully');
    }

    public function update(TaskUpdaterequest $request, Project $project, Objective $objective, Task $task): JsonResponse {
        Gate::authorize('updateTask', [Task::class, $project, $objective, $task]);

        $task->update($request->validated());

        return $this->sendApiResponse($task, 'Task updated successfully');
    }


    public function cancelTask(Request $request, Task $task){

        Gate::authorize('cancelTask', $task);

        return response()->json([
            'message' => 'Task updated successfully',
            'task' => $task,
        ], 418);
    }

    public function updateStatus(Request $request, Task $task){
        // Update task status logic here

        Gate::authorize('updateStatus', [$task, $request->status]);

        return response()->json([
            'message' => 'Task status updated successfully',
            'task' => $task,
        ], 418);
    }
}
