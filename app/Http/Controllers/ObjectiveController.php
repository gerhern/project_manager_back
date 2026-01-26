<?php

namespace App\Http\Controllers;

use App\enums\ObjectiveStatus;
use App\Http\Requests\ObjectiveStoreRequest;
use App\Models\Project;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\Objective;
use Illuminate\Support\Facades\Gate;

class ObjectiveController extends Controller
{
    use ApiResponse;

    public function index(Request $request, Project $project): JsonResponse {
        Gate::authorize('viewProject', $project);

        $objectives = $project->objectives()
            ->withCount('tasks')
            ->latest()
            ->get();
        return $this->sendApiResponse($objectives, 'Projects retrieved successfully');
    }

    public function store(ObjectiveStoreRequest $request, Project $project){
        Gate::authorize('createObjective', $project);

        $objective = Objective::create([
            'title'         => $request->title,
            'description'   => $request->description,
            'status'        => ObjectiveStatus::NotCompleted->name,
            'project_id'    => $project->id
        ]);

        return $this->sendApiResponse($objective, 'Objective created successfully', 201);
    }

    public function update(Request $request, Objective $objective){

        Gate::authorize('updateObjective', $objective);
        return $this->sendApiResponse($objective, 'Objective updated successfully');
    }
}
