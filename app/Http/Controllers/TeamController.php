<?php

namespace App\Http\Controllers;

use App\Enums\TeamStatus;
use App\Http\Requests\TeamStoreRequest;
use App\Http\Requests\TeamUpdateRequest;
use App\Models\Team;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Spatie\Permission\Models\Role;

class TeamController extends Controller
{
    use ApiResponse;
    public function index(Request $request){
        $teams = $request->user()->teams()->withPivot('role_id')->get();
        return $this->sendApiResponse($teams, "Data Retrieved Successfuly", 200);
    }

    public function store(TeamStoreRequest $request): JsonResponse{
        try{
            \DB::beginTransaction();
            $adminRoleId = Role::where('name', 'Admin')->first('id')->id;
            $team = Team::create([
                'name' => $request->name,
                'description' => $request->description ?? null,
                'status' => TeamStatus::Active
            ]);

            $request->user()->teams()->attach($team->id, [
                'role_id' => $adminRoleId 
            ]);

            \DB::commit();
            return $this->sendApiResponse($team, 'Team created successfully', 201);

        } catch(\Exception $e){
            \DB::rollBack();

            \Log::error('Error creating team: '. $e->getMessage());
            return $this->sendApiError( 'Could not create team', 500);
        }
    }

    public function update(TeamUpdateRequest $request, Team $team): JsonResponse{
        Gate::authorize('updateTeam', $team);
        try{
            $team->update($request->validated());
            return $this->sendApiResponse($team, 'Team updated successfully.', 200);

        }catch(\Exception $e){
            \Log::error('Error, can not updated team: '.$e->getMessage());
            return $this->sendApiError('Error, can not updated team', 403);
        }
    }

    public function inactiveTeam(Request $request, Team $team): JsonResponse {
        Gate::authorize('inactiveTeam', $team);

        $team->update(['status' => TeamStatus::Inactive->name]);

        return $this->sendApiResponse([],'Team inactivated successfully');
    }

    
}
