<?php

namespace App\Http\Controllers;

use App\Enums\TaskStatus;
use App\Http\Requests\TaskStoreRequest;
use App\Http\Requests\TaskUpdaterequest;
use App\Http\Resources\TaskResource;
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

    public function index(Request $request, Objective $objective): JsonResponse {
        Gate::authorize('viewTasks', [Task::class, $objective]);
        $tasks = $objective->tasks;
        return $this->sendApiResponse(TaskResource::collection($tasks), 'Tasks retrieved successfully');
    }

    public function store(TaskStoreRequest $request, Objective $objective): JsonResponse {
        Gate::authorize('createTask', [Task::class, $objective]);

        
        $task = Task::create(
            $request->validated() + [
                'objective_id'  => $objective->id,
                'status'        => $request->filled('user_id') ? TaskStatus::Assigned: TaskStatus::Pending
            ]);

        return $this->sendApiResponse(new TaskResource($task), 'Task created successfully', 201);
    }

    public function show(Request $request, Objective $objective, Task $task): JsonResponse {
        Gate::authorize('viewTask', [Task::class, $objective, $task]);
        return $this->sendApiResponse(new TaskResource($task), 'Task retrieved successfully');
    }

    public function update(TaskUpdaterequest $request, Objective $objective, Task $task): JsonResponse {
        Gate::authorize('updateTask', [Task::class, $objective, $task]);

        $data = $request->validated();

        if ($request->has('user_id')) {
            $data['status'] = is_null($request->user_id) 
                ? TaskStatus::Pending 
                : TaskStatus::Assigned;
        }

        $task->update($data);

        return $this->sendApiResponse(new TaskResource($task), 'Task updated successfully');
    }


    public function cancelTask(Request $request, Objective $objective, Task $task): JsonResponse{

        Gate::authorize('cancelTask', [Task::class, $objective, $task]);
        $task->update(['status' => TaskStatus::Canceled->name]);
        return $this->sendApiResponse(new TaskResource($task), 'Task canceled successfully');
    }

    public function updateStatus(Request $request, Objective $objective, Task $task): JsonResponse{
        Gate::authorize('updateTaskStatus', [Task::class, $objective, $task, $request->status]);

        $task->transitionStatus(TaskStatus::from($request->status));
        $task->save();

        return $this->sendApiResponse(new TaskResource($task), 'Task status updated successfully', 200);
    }
}
