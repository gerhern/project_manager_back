<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\{Project, ProjectDispute};
use Illuminate\Support\Facades\Gate;

class ProjectController extends Controller
{
    public function update(Request $request, Project $project)
    {

    Gate::authorize('updateProject', $project);
    
        return response()->json([
            'message' => 'Project updated successfully',
            'project' => $project
        ], 200);
        
    }

    public function resolveDispute(Request $request, ProjectDispute $dispute){

        Gate::authorize('updateDisputeStatus', $dispute);

        return response()->json([
            'message' => 'Dispute resolved successfully',
            'dispute' => $dispute
        ], 200);
    }

    public function show(Request $request, Project $project){
        Gate::authorize('viewProject', $project);

        return response()->json([
            'project' => $project,
            'message' => 'Project retrieved successfully',
        ], 200);
    }
}
