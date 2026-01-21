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
    /**
     * Create objects Project, Team and User, assign created user as a owner of project and assign project on projects list's team
     * This function uses createTeam() function; This function only create and links users using pivot user_id on projects and teams
     * NOT links using memberships table
     * @param array $attributesProject Array of projects' attributes override default factory values'
     * @param array $attributesTeam Array of teams' attributes override default factorycvalues'
     * @return array<mixed|Project|\Illuminate\Database\Eloquent\Collection<int, Project>>
     */
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

    /**
     * create a new register on memberships table linking user with teams 
     * @param Team $team New user will be added to this $team
     * @param User $user User to add to the provided team
     * @param string $rolName The user will be added to the team with this role (Role must exists)
     * @return void
     */
    public function addUserToTeam(Team $team, User $user, string $rolName = 'Admin'): void
    {
        $rolId = Role::where('name', $rolName)->first()->id;
        $user->teams()->attach($team->id, ['role_id' => $rolId]);
    }
    
    /**
     * Retrieves a role model by role name
     * @param string $rolname Role name must be the same for a existing role
     * @return Role|null
     */
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

    /**
     * Creates a User and Team objects, they don't related, just two single models
     * @param array $attributes array of attributes to override factory default
     * @return array<Team|User|\Illuminate\Database\Eloquent\Collection<int, Team>|\Illuminate\Database\Eloquent\Collection<int, User>>
     */
    public function createTeam(array $attributes = []): array
    {
        $user = User::factory()->create();
        $team = Team::factory()->create($attributes);

        return [$user, $team];
    }
}
