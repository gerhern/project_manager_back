<?php

namespace App\Http\Controllers;

use App\Enums\RoleList;
use App\Enums\TeamStatus;
use App\Http\Requests\TeamStoreRequest;
use App\Http\Requests\TeamUpdateRequest;
use App\Http\Resources\TeamResource;
use App\Models\Team;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Spatie\Permission\Models\Role;

class TeamController extends Controller
{
    use ApiResponse;
    public function index(Request $request): JsonResponse{
        $teams = $request->user()->teams()->withPivot('role_id')->get();
        return $this->sendApiResponse(TeamResource::collection($teams), "Data retrieved successfuly");
    }

    public function store(TeamStoreRequest $request): JsonResponse{
        try{
            \DB::beginTransaction();
            $adminRoleId = Role::where('name', RoleList::Owner->value)->first('id')->id;
            $team = Team::create([
                'name' => $request->name,
                'description' => $request->description ?? null,
                'status' => TeamStatus::Active
            ]);

            $request->user()->teams()->attach($team->id, [
                'role_id' => $adminRoleId 
            ]);

            \DB::commit();
            return $this->sendApiResponse(new TeamResource($team), 'Team created successfully', 201);

        } catch(\Exception $e){
            \DB::rollBack();

            \Log::error('Error creating team: '. $e->getMessage());
            return $this->sendApiError( 'Could not create team', 500);
        }
    }

    public function show(Request $request, Team $team): JsonResponse{
        return $this->sendApiResponse(new TeamResource($team), 'Team retrieved successfully');
    }

    public function update(TeamUpdateRequest $request, Team $team): JsonResponse{
        Gate::authorize('updateTeam', [Team::class, $team]);
        
        $team->update($request->validated());

        return $this->sendApiResponse(new TeamResource($team), 'Team updated successfully.', 200);
    }

    public function destroy(Request $request, Team $team): JsonResponse {
        Gate::authorize('inactiveTeam', [Team::class, $team]);

        $team->update(['status' => TeamStatus::Inactive->name]);

        return $this->sendApiResponse(new TeamResource($team),'Team inactivated successfully');
    }

    
}
