<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project;

class ProjectController extends Controller
{
    public function update(Request $request, Project $project)
    {
    
        return response()->json([
            'message' => 'Project updated successfully',
            'project' => $project
        ], 418);
        
    }
}
