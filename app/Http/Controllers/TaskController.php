<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

use App\Models\Task;

class TaskController extends Controller
{
    public function update(Request $request, Task $task){
        // Update task logic here

        return response()->json([
            'message' => 'Task updated successfully',
            'task' => $task,
        ], 200);
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
