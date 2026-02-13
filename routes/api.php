<?php

use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\{ EnsureHierarchyIsPermitted};
use App\Http\Controllers\{ProjectController, ObjectiveController, TaskController, TeamController};


Route::post('/login', [AuthController::class, 'login'])
    ->name('login');


Route::middleware('auth:sanctum')->group(function(){

    Route::post('/logout', [AuthController::class, 'logout'])
    ->name('logout');

    //Projects
    Route::get('/projects', [ProjectController::class, 'index'])
        ->name('projects.index');

    Route::post('/teams/{team}/projects', [ProjectController::class, 'store'])
        ->middleware(EnsureHierarchyIsPermitted::class)
        ->scopeBindings()
        ->name('projects.store');

    Route::match(['PUT', 'PATCH'], '/teams/{team}/projects/{project}', [ProjectController::class, 'update'])
        ->middleware(EnsureHierarchyIsPermitted::class)
        ->scopeBindings()
        ->name('projects.update');

    Route::get('/teams/{team}/projects/{project}', [ProjectController::class, 'show'])
        ->scopeBindings()
        ->name('project.show');

    Route::delete('teams/{team}/projects/{project}', [ProjectController::class, 'destroy'])
        ->middleware(EnsureHierarchyIsPermitted::class)
        ->name('projects.cancel');

    //Objectives
    Route::get('/projects/{project}/objectives', [ObjectiveController::class, 'index'])
        ->name('projects.objectives.index');

    Route::post('/projects/{project}/objectives', [ObjectiveController::class, 'store'])
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
    Route::get('objectives/{objective}/tasks', [TaskController::class, 'index'])
        ->name('projects.objectives.tasks.index')
        ->scopeBindings();

    Route::post('/objectives/{objective}/tasks', [TaskController::class, 'store'])
        ->name('projects.objectives.tasks.store')
        ->middleware(EnsureHierarchyIsPermitted::class)
        ->scopeBindings();

    Route::get('objectives/{objective}/tasks/{task}', [TaskController::class, 'show'])
        ->name('projects.objectives.tasks.show')
        ->scopeBindings();

    Route::match(['PUT', 'PATCH'], 'objectives/{objective}/tasks/{task}', [TaskController::class, 'update'])
        ->middleware(EnsureHierarchyIsPermitted::class)
        ->name('projects.objectives.tasks.update')
        ->scopeBindings();

    Route::delete( 'objectives/{objective}/tasks/{task}', [TaskController::class, 'cancelTask'])
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

    Route::post('/teams', [TeamController::class, 'store'])
        ->name('teams.store');

    Route::get('/teams/{team}', [TeamController::class, 'show'])
        ->name('teams.show');

    Route::match(['PUT', 'PATCH'], '/teams/{team}', [TeamController::class, 'update'])
        ->middleware(EnsureHierarchyIsPermitted::class)
        ->name('teams.update');

    Route::delete('teams/{team}', [TeamController::class, 'destroy'])
        ->middleware(EnsureHierarchyIsPermitted::class)
        ->name('teams.inactive');
});
