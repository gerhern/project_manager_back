<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\{ EnsureHierarchyIsPermitted};
use App\Http\Controllers\{ProjectController, ObjectiveController, TaskController, TeamController};


//Projects
Route::get('/projects/index', [ProjectController::class, 'index'])
    ->name('projects.index');

Route::post('/projects/store', [ProjectController::class, 'store'])
    ->middleware(EnsureHierarchyIsPermitted::class)
    ->name('projects.store');

Route::match(['PUT', 'PATCH', 'DELETE'], '/projects/{project}', [ProjectController::class, 'update'])
    ->middleware(EnsureHierarchyIsPermitted::class)
    ->name('projects.update');

Route::get('/projects/show/{project}', [ProjectController::class, 'show'])
    ->middleware(EnsureHierarchyIsPermitted::class)
    ->name('project.show');

Route::delete('/projects/cancel/{project}', [ProjectController::class, 'cancel'])
    ->middleware(EnsureHierarchyIsPermitted::class)
    ->name('projects.cancel');

//Objectives
Route::get('/projects/{project}/objectives', [ObjectiveController::class, 'index'])
    ->name('projects.objectives.index');

Route::post('/projects/{project}/objectives/store', [ObjectiveController::class, 'store'])
    ->middleware(EnsureHierarchyIsPermitted::class)
    ->name('projects.objectives.store');

Route::match(['PUT', 'PATCH'],'projects/{project}/objectives/{objective}', [ObjectiveController::class, 'update'])
    ->middleware(EnsureHierarchyIsPermitted::class)
    ->name('projects.objectives.update');

Route::get('projects/{project}/objectives/{objective}', [ObjectiveController::class, 'show'])
    ->name('projects.objectives.show');

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

//Teams
Route::get('/teams/index', [TeamController::class, 'index'])
    ->name('teams.index');

Route::post('/teams/create', [TeamController::class, 'store'])
    ->name('teams.store');

Route::match(['PUT', 'PATCH'], '/teams/update/{team}', [TeamController::class, 'update'])
    ->middleware(EnsureHierarchyIsPermitted::class)
    ->name('teams.update');

Route::delete('teams/inactive/{team}', [TeamController::class, 'inactiveTeam'])
    ->middleware(EnsureHierarchyIsPermitted::class)
    ->name('teams.inactive');

