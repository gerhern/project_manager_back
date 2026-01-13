<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\{CheckStatus, EnsureHierarchyIsPermitted};
use App\Http\Controllers\{ProjectController, ObjectiveController, TaskController};


//Projects
Route::match(['PUT', 'PATCH', 'DELETE'], '/projects/{project}', [ProjectController::class, 'update'])
    ->middleware(EnsureHierarchyIsPermitted::class)
    ->name('project.update');

//Objectives
Route::match(['PUT', 'PATCH', 'DELETE'],'/objectives/{objective}', [ObjectiveController::class, 'update'])
    ->middleware(EnsureHierarchyIsPermitted::class)
    ->name('objective.update');

//Tasks
Route::match(['PUT', 'PATCH', 'DELETE'], '/tasks/{task}', [TaskController::class, 'update'])
    ->middleware(EnsureHierarchyIsPermitted::class)
    ->name('task.update');
    
// Route::get('/projects/test', function (Request $request) {
//     return response()->json(['message' => 'API is working']);
// });

// Route::get('/user', function (Request $request) {
//     return $request->user(); 
// })->middleware('auth:sanctum');
