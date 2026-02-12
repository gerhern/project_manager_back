<?php

namespace App\Http\Controllers;

use App\Enums\ObjectiveStatus;
use App\Http\Requests\ObjectiveStoreRequest;
use App\Http\Requests\ObjectiveUpdateRequest;
use App\Http\Resources\ObjectiveResource;
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
        Gate::authorize('indexObjective', [Objective::class, $project]);

        $objectives = $project->objectives()
            ->withCount('tasks')
            ->latest()
            ->get();
        return $this->sendApiResponse(ObjectiveResource::collection($objectives), 'Objectives retrieved successfully');
    }

    public function store(ObjectiveStoreRequest $request, Project $project): JsonResponse{

        Gate::authorize('createObjective', [Objective::class, $project]);


            $objective = Objective::create([
                'title'         => $request->title,
                'description'   => $request->description,
                'status'        => ObjectiveStatus::NotCompleted,
                'priority'      => $request->priority,
                'project_id'    => $project->id
            ]);
    
            return $this->sendApiResponse(new ObjectiveResource($objective), 'Objective created successfully', 201);
    }

    public function update(ObjectiveUpdateRequest $request, Project $project, Objective $objective):JsonResponse{
        Gate::authorize('updateObjective', [$objective, $project]);

            $objective->update($request->validated());
            return $this->sendApiResponse($objective, 'Objective updated successfully');
    }

    public function show(Request $request, Project $project, Objective $objective): JsonResponse{
        Gate::authorize('viewObjective', [Objective::class, $project, $objective]);
        $objective->load('tasks');
        return $this->sendApiResponse(new ObjectiveResource($objective),'Objective retrieved successfully');
    }

    public function cancel(Request $request, Project $project, Objective $objective): JsonResponse{
        Gate::authorize('cancelObjective', [$objective, $project]);
        $objective->update(['status' => ObjectiveStatus::Canceled]);
        return $this->sendApiResponse(new ObjectiveResource($objective), 'Objective has been canceled susccessfully');
    }
}
