<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\{ EnsureHierarchyIsPermitted};
use App\Http\Controllers\{ProjectController, ObjectiveController, TaskController};


//Projects
Route::match(['PUT', 'PATCH', 'DELETE'], '/projects/{project}', [ProjectController::class, 'update'])
    ->middleware(EnsureHierarchyIsPermitted::class)
    ->name('project.update');

Route::get('/projects/show/{project}', [ProjectController::class, 'show'])
    ->middleware(EnsureHierarchyIsPermitted::class)
    ->name('project.show');

//Objectives
Route::match(['PUT', 'PATCH', 'DELETE'],'/objectives/{objective}', [ObjectiveController::class, 'update'])
    ->middleware(EnsureHierarchyIsPermitted::class)
    ->name('objective.update');

//Tasks
Route::match(['PUT', 'PATCH', 'DELETE'], '/tasks/{task}', [TaskController::class, 'update'])
    ->middleware(EnsureHierarchyIsPermitted::class)
    ->name('task.update');

Route::match(['PUT', 'PATCH', 'DELETE'], '/tasks/cancel/{task}', [TaskController::class, 'cancelTask'])
    ->middleware(EnsureHierarchyIsPermitted::class)
    ->name('task.cancel');

Route::match(['PUT', 'PATCH', 'DELETE'], '/tasks/update-status/{task}', [TaskController::class, 'updateStatus'])
    ->middleware(EnsureHierarchyIsPermitted::class)
    ->name('task.updateStatus');

//Disputes
Route::match(['PUT', 'PATCH'], '/projects/dispute/{dispute}', [ProjectController::class, 'resolveDispute'])
    ->name('dispute.resolve');

