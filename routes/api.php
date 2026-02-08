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

Route::match(['PUT', 'PATCH'], '/projects/{project}', [ProjectController::class, 'update'])
    ->middleware(EnsureHierarchyIsPermitted::class)
    ->name('projects.update');

Route::get('/projects/show/{project}', [ProjectController::class, 'show'])
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
    ->name('projects.objectives.update')
    ->scopeBindings();

Route::get('projects/{project}/objectives/{objective}', [ObjectiveController::class, 'show'])
    ->name('projects.objectives.show')
    ->scopeBindings();

Route::delete('projects/{project}/objectives/{objective}', [ObjectiveController::class, 'cancel'])
    ->middleware(EnsureHierarchyIsPermitted::class)
    ->name('projects.objectives.delete')
    ->scopeBindings();

//Tasks
Route::get('projects/{project}/objectives/{objective}/tasks', [TaskController::class, 'index'])
    ->name('projects.objectives.tasks.index')
    ->scopeBindings();

Route::post('projects/{project}/objectives/{objective}/store', [TaskController::class, 'store'])
    ->name('projects.objectives.tasks.store')
    ->middleware(EnsureHierarchyIsPermitted::class)
    ->scopeBindings();

Route::get('projects/{project}/objectives/{objective}/tasks/{task}', [TaskController::class, 'show'])
    ->name('projects.objectives.tasks.show')
    ->scopeBindings();

Route::match(['PUT', 'PATCH'], 'projects/{project}/objectives/{objective}/tasks/{task}/update', [TaskController::class, 'update'])
    ->middleware(EnsureHierarchyIsPermitted::class)
    ->name('projects.objectives.tasks.update')
    ->scopeBindings();

Route::delete( 'projects/{project}/objectives/{objective}/tasks/{task}/cancel', [TaskController::class, 'cancelTask'])
    ->middleware(EnsureHierarchyIsPermitted::class)
    ->name('projects.objectives.tasks.delete')
    ->scopeBindings();

Route::match(
        ['PUT', 'PATCH'],
        'projects/{project}/objectives/{objective}/tasks/{task}/status', 
        [TaskController::class, 'updateStatus']
    )->middleware(EnsureHierarchyIsPermitted::class)
    ->name('projects.objectives.tasks.status')
    ->scopeBindings();

//Disputes
Route::match(['PUT', 'PATCH'], '/projects/dispute/{dispute}', [ProjectController::class, 'resolveDispute'])
    ->name('dispute.resolve');

//Teams
Route::get('/teams', [TeamController::class, 'index'])
    ->name('teams.index');

Route::post('/teams/create', [TeamController::class, 'store'])
    ->name('teams.store');

Route::get('/teams/{team}', [TeamController::class, 'show'])
    ->name('teams.show');

Route::match(['PUT', 'PATCH'], '/teams/{team}/update', [TeamController::class, 'update'])
    ->middleware(EnsureHierarchyIsPermitted::class)
    ->name('teams.update');

Route::delete('teams/inactive/{team}', [TeamController::class, 'inactiveTeam'])
    ->middleware(EnsureHierarchyIsPermitted::class)
    ->name('teams.inactive');

