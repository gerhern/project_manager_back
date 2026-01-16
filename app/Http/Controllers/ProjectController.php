<?php

namespace App\Http\Controllers;

use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Models\{Project, ProjectDispute};
use Illuminate\Support\Facades\Gate;

class ProjectController extends Controller
{
    use ApiResponse;
    public function update(Request $request, Project $project)
    {

        Gate::authorize('updateProject', $project);
        
        return $this->sendApiResponse($project, 'Project updated successfully');
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
        return $this->sendApiResponse($project, 'Project retrieved successfully');
    }
}
