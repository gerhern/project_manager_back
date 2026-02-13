<?php

namespace App\Http\Controllers;

use App\Enums\{DisputeStatus, ProjectStatus, RoleList};
use App\Http\Requests\ProjectStoreRequest;
use App\Http\Requests\ProjectUpdateRequest;
use App\Http\Resources\DisputeResource;
use App\Http\Resources\ProjectResource;
use App\Notifications\DisputeStartNotification;
use App\Traits\ApiResponse;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\{Project, ProjectDispute, Team};
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class ProjectController extends Controller
{
    use ApiResponse;

    /**
     * Retrieves every project linked to logged user
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $projects = $request->user()->projects()->withPivot('role_id')->with('team')->get();
        return $this->sendApiResponse(ProjectResource::collection($projects), 'Projects retrieved successfully');
    }

    /**
     * Create new Project on database, only team admin can create new projects
     * @param ProjectStoreRequest $request
     * @return void
     */
    public function store(ProjectStoreRequest $request, Team $team): JsonResponse
    {
        Gate::authorize('createProject', [Team::class, $team]);
        try {
            \DB::beginTransaction();
            $managerRoleId = Role::where('name', RoleList::Manager->value)->first('id')->id;
            $project = Project::create([
                'name' => $request->name,
                'description' => $request->description ?? null,
                'status' => ProjectStatus::Active,
                'team_id' => $team->id,
                'user_id' => $request->user()->id
            ]);

            $request->user()->projects()->attach($project->id, [
                'role_id' => $managerRoleId
            ]);

            \DB::commit();
            return $this->sendApiResponse(new ProjectResource($project), 'Project created successfully', 201);

        } catch (\Exception $e) {
            \DB::rollBack();

            \Log::error('Error creating project: ' . $e->getMessage());
            return $this->sendApiError('Could not create project', 500);
        }

    }
    public function update(ProjectUpdateRequest $request, Team $team, Project $project): JsonResponse
    {
        Gate::authorize('updateProject', [Project::class, $team, $project]);
            $project->update($request->validated());
            return $this->sendApiResponse(new ProjectResource($project), 'Project updated successfully', 200);
    }

    public function resolveDispute(Request $request, ProjectDispute $dispute): JsonResponse
    {
        Gate::authorize('updateDisputeStatus', $dispute);
        $request->validate([
            'status' => [
                'required',
                Rule::in(DisputeStatus::resolutionStates())
            ]
        ]);

        try {
            \DB::beginTransaction();

            $isAccepted = $request->status === DisputeStatus::Accepted->value;
    
            $dispute->project->update([
                'status' => $isAccepted ? ProjectStatus::Canceled : ProjectStatus::Active
            ]);
    
            $dispute->update(['status' => $request->status]);

            \Db::commit();
    
            return $this->sendApiResponse(new DisputeResource($dispute), 'Dispute '. ($isAccepted ? 'resolved' : 'rejected') . ' successfully');
        }catch(\Exception $e){
            \Db::rollBack();
            \Log::error('Error trying to close dispute: '.$e->getMessage());
            return $this->sendApiError('Error trying to close dispute');
        }
    }

    public function show(Request $request,Team $team, Project $project): JsonResponse
    {
        Gate::authorize('viewProject', [Project::class, $team, $project]);
        return $this->sendApiResponse(new ProjectResource($project), 'Project retrieved successfully');
    }

    public function destroy(Request $request, Team $team, Project $project): JsonResponse {
        Gate::authorize('cancelProject', $project);

        if($request->user()->id === $project->user_id){
            $project->update([
                'status' => ProjectStatus::Canceled
            ]);

            return $this->sendApiResponse(new ProjectResource($project), 'The project has been canceled successfully');
        }

        try{
            \DB::beginTransaction();
             ProjectDispute::create([
            'project_id'    => $project->id,
            'user_id'       => $request->user()->id,
            'expired_at'    => Carbon::now()->addDays(15),
            'status'        => DisputeStatus::Open
            ]);

            $project->update([
                    'status' => ProjectStatus::CancelInProgress
                ]);
            \DB::commit();

            $project->creator->notify(new DisputeStartNotification($project));

            return $this->sendApiResponse(new ProjectResource($project), 'An open dispute has been created');
        }catch(\Exception $e){
            \DB::rollBack();
            \Log::error('Error trying to cancel project: '.$e->getMessage());
            return $this->sendApiError('Error trying to cancel project');
        }
    }
}
