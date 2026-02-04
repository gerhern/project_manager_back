<?php

namespace App\Http\Controllers;

use App\Enums\DisputeStatus;
use App\Enums\ProjectStatus;
use App\Http\Requests\ProjectStoreRequest;
use App\Http\Requests\ProjectUpdateRequest;
use App\Models\Team;
use App\Traits\ApiResponse;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\{Project, ProjectDispute};
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
        $projects = $request->user()->projects()->withPivot('role_id')->get();
        return $this->sendApiResponse($projects, 'Projects retrieved successfully');
    }

    /**
     * Create new Project on database, only team admin can create new projects
     * @param ProjectStoreRequest $request
     * @return void
     */
    public function store(ProjectStoreRequest $request): JsonResponse
    {
        Gate::authorize('createProject', Team::find($request->team_id));
        try {
            \DB::beginTransaction();
            $managerRoleId = Role::where('name', 'Manager')->first('id')->id;
            $project = Project::create([
                'name' => $request->name,
                'description' => $request->description ?? null,
                'status' => ProjectStatus::Active,
                'team_id' => $request->team_id,
                'user_id' => $request->user()->id
            ]);

            $request->user()->projects()->attach($project->id, [
                'role_id' => $managerRoleId
            ]);

            \DB::commit();
            return $this->sendApiResponse($project, 'Project created successfully', 201);

        } catch (\Exception $e) {
            \DB::rollBack();

            \Log::error('Error creating project: ' . $e->getMessage());
            return $this->sendApiError('Could not create project', 500);
        }

    }
    public function update(ProjectUpdateRequest $request, Project $project): JsonResponse
    {
        Gate::authorize('updateProject', $project);
            $project->update($request->validated());
            return $this->sendApiResponse($project, 'Project updated successfully', 200);
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

            $isAccepted = $request->status === DisputeStatus::Accepted->name;
    
            $dispute->project->update([
                'status' => $isAccepted ? ProjectStatus::Canceled : ProjectStatus::Active
            ]);
    
            $dispute->update(['status' => $request->status]);

            \Db::commit();
    
            return $this->sendApiResponse($dispute, 'Dispute '. ($isAccepted ? 'resolved' : 'rejected') . ' successfully');
        }catch(\Exception $e){
            \Db::rollBack();
            \Log::error('Error trying to close dispute: '.$e->getMessage());
            return $this->sendApiError('Error trying to close dispute');
        }
    }

    public function show(Request $request, Project $project): JsonResponse
    {
        Gate::authorize('viewProject', $project);
        return $this->sendApiResponse($project, 'Project retrieved successfully');
    }

    public function cancel(Request $request, Project $project): JsonResponse {
        Gate::authorize('cancelProject', $project);

        if($request->user()->id === $project->user_id){
            $project->update([
                'status' => ProjectStatus::Canceled->name
            ]);

            return $this->sendApiResponse($project, 'The project has been canceled successfully');
        }

        try{
            \DB::beginTransaction();
             ProjectDispute::create([
            'project_id'    => $project->id,
            'user_id'       => $request->user()->id,
            'expired_at'    => Carbon::now()->addDays(15)->toTimeString(),
            'status'        => DisputeStatus::Open->name
            ]);

            $project->update([
                    'status' => ProjectStatus::CancelInProgress->name
                ]);
            \DB::commit();
            return $this->sendApiResponse($project, 'An open dispute has been created');
        }catch(\Exception $e){
            \DB::rollBack();
            \Log::error('Error trying to cancel project: '.$e->getMessage());
            return $this->sendApiError('Error trying to cancel project');
        }
    }
}
