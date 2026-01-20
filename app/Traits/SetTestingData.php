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
    public function createProject(array $attributesProject = [], array $attributesTeam = []): array
    {

        [$user, $team] = $this->createTeam($attributesTeam);

        $defaultAttributes = [
            'user_id' => $user->id,
            'team_id' => $team->id
        ];

        $project = Project::factory()->create(
            array_merge($defaultAttributes, $attributesProject)
        );

        return [$user, $team, $project];
    }

    public function addUserToProject(Project $project, User $user, string $rolName = 'Manager'): void
    {
        $rolId = Role::where('name', $rolName)->first()->id;
        $user->projects()->attach($project->id, ['role_id' => $rolId]);
    }

    public function addUserToTeam(Team $team, User $user, string $rolName = 'Admin'): void
    {
        $rolId = Role::where('name', $rolName)->first()->id;
        $user->teams()->attach($team->id, ['role_id' => $rolId]);
    }
    
    public function getRole(string $rolname): Role
    {
        return Role::where('name', $rolname)->first();
    }

    public function createObjective(array $attributesObjective = [], array $attributesProject = [], array $attributesTeam = []): array
    {
        [$user, $team, $project] = $this->createProject($attributesProject,$attributesTeam);
        $defaultAttributes = [
            'project_id' => $project->id
        ];

        $objective = Objective::factory()->create(
            array_merge($defaultAttributes, $attributesObjective)
        );

        return [$user, $team, $project, $objective];
    }

    public function createNewObjetiveOnProject(Project $project): Objective
    {
        return Objective::factory()->create([
            'project_id' => $project->id
        ]);
    }

    public function createNewProjectOnTeam(Team $team, User $user): Project
    {
        return Project::factory()->create([
            'team_id' => $team->id,
            'user_id' => $user->id
        ]);
    }

    public function createTeam(array $attributes = []): array
    {
        $user = User::factory()->create();
        $team = Team::factory()->create($attributes);

        return [$user, $team];
    }
}
