<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\CheckStatus;
use App\Http\Controllers\ProjectController;

Route::put('/projects/{project}', [ProjectController::class, 'update'])->name('projects.update')->middleware(CheckStatus::class);    
// Route::get('/projects/test', function (Request $request) {
//     return response()->json(['message' => 'API is working']);
// });

// Route::get('/user', function (Request $request) {
//     return $request->user(); 
// })->middleware('auth:sanctum');
