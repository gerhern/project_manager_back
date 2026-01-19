<?php

namespace App\Traits;

use App\Enums\ProjectStatus;
use App\Enums\TeamStatus;
use App\Models\Objective;
use App\Models\Project;
use App\Models\Team;
use App\Models\User;
use Database\Seeders\RolesSeeder;
use Spatie\Permission\Models\Role;

trait SetTestingData
{
    public function createProject(): array{
        $team = Team::factory()->create();
        $user = User::factory()->create();
        $project = Project::factory()->create([
            'user_id' => $user->id,
            'team_id' => $team->id
        ]);

        return [$user, $team, $project];
    }

    public function addUserToProject(Project $project, User $user, string $rolName = 'Manager'): void{
        $rolId = Role::where('name', $rolName)->first()->id;
        $user->projects()->attach($project->id, ['role_id' => $rolId]);
    }

    public function addUserToTeam(Team $team, User $user, string $rolName = 'Admin'): void {
        $rolId = Role::where('name', $rolName)->first()->id;
        $user->teams()->attach($team->id, ['role_id' => $rolId]);
    }

    public function getRole(string $rolname): Role {
        return Role::where('name', $rolname)->first();
    }

    public function createObjective(){
        $team = Team::factory()->create();
        $user = User::factory()->create();
        $project = Project::factory()->create([
            'user_id' => $user->id,
            'team_id' => $team->id
        ]);
        $objective = Objective::factory()->create([
            'project_id' => $project->id
        ]);

        return [$user, $team, $project, $objective];
    }

    public function createNewObjetiveOnProject(Project $project): Objective{
        return Objective::factory()->create([
            'project_id' => $project->id
        ]);
    }

    public function createNewProjectOnTeam(Team $team, User $user): Project{
        return Project::factory()->create([
            'team_id' => $team->id,
            'user_id' => $user->id
        ]);
    }
}
