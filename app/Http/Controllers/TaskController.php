<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Task;

class TaskController extends Controller
{
    public function update(Request $request, Task $task){
        return response()->json([
            'message' => 'Task updated successfully',
            'task' => $task,
        ], 418);
    }
}
