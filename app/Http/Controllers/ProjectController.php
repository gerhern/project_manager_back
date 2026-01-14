<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\{Project, ProjectDispute};
use Illuminate\Support\Facades\Gate;

class ProjectController extends Controller
{
    public function update(Request $request, Project $project)
    {
    
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
}
