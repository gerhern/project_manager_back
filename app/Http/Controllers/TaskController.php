<?php

namespace App\Http\Controllers;

use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

use App\Models\Task;

class TaskController extends Controller
{
    use ApiResponse;
    public function update(Request $request, Task $task){
        
        Gate::authorize('updateTask', $task);

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
