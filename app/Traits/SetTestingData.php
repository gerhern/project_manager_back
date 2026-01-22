<?php

namespace App\Traits;

use App\Enums\DisputeStatus;
use App\Enums\ProjectStatus;
use App\Enums\TeamStatus;
use App\Models\Objective;
use App\Models\Project;
use App\Models\ProjectDispute;
use App\Models\Task;
use App\Models\Team;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\RolesSeeder;
use Spatie\Permission\Models\Role;

trait SetTestingData
{

    protected static array $testingRoles = [];

    protected function getCachedRoleId(string $name): int
    {
        if (!isset(self::$testingRoles[$name])) {
            self::$testingRoles[$name] = Role::where('name', $name)->firstOrFail()->id;
        }
        return self::$testingRoles[$name];
    }

    public function setRoles(... $roleList): void{

        foreach ($roleList as $roleName) { 
            Role::create(['name' => $roleName]);
        }
    }

    public function createUserWithRole(string $role): User{
        return User::factory()->create()->assignRole($role);
    }


    /**
     * Create objects Project, Team and User, assign created user as a owner of project and assign project on projects list's team
     * This function uses createTeam() function; This function only create and links users using pivot user_id on projects and teams
     * NOT links using memberships table
     * @param array $attributesProject Array of projects' attributes override default factory values'
     * @param array $attributesTeam Array of teams' attributes override default factorycvalues'
     * @return array<mixed|Project|\Illuminate\Database\Eloquent\Collection<int, Project>>
     */
    public function createProject(array $attributesProject = [], User $user = null, Team $team = null): array
    {
        $user ??= User::factory()->create();
        $team ??= Team::factory()->create();

        $project = Project::factory()->create(
            array_merge([
                'user_id' => $user->id,
                'team_id' => $team->id
            ], $attributesProject));

        return [$user, $team, $project];
    }

    public function addUserToProject(Project $project, User $user, string $rolName = 'Manager'): void
    {
        $user->projects()->attach($project->id, ['role_id' => $this->getCachedRoleId($rolName)]);
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
        $user->teams()->attach($team->id, ['role_id' => $this->getCachedRoleId($rolName)]);
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

    public function createObjective(array $attributes = [], User $user = null, Project $project = null, Team $team = null): array
    {
        if(!$project){
            [$user, $team, $project] = $this->createProject([],$user, $team);
        }

        $objective = Objective::factory()->create(array_merge([
            'project_id' => $project->id
        ], $attributes));

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

    public function createTask(array $attributes = [], User $user = null, Team $team = null, Project $project = null, Objective $objective = null): array {
        
        if(!$objective){
            [$user, $team, $project, $objective] = $this->createObjective([], $user, $team, $project);
        }

        $task = Task::factory()->create(array_merge([
            'objective_id' => $objective->id
        ], $attributes));

        return [$user, $team, $project, $objective, $task];
    }

    public function createDispute(Project $project, User $user): ProjectDispute {
        return ProjectDispute::create([
            'project_id'    => $project->id,
            'user_id'       => $user->id,
            'expired_at'    => Carbon::now()->addDays(15)->toTimeString(),
            'status'        => DisputeStatus::Open->name
        ]);
    }
}
