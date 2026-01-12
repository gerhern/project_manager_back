<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\Task;
use App\Models\Team;
use App\Models\User;
use App\Models\Objective;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CheckRelatedModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_projects_only_returns_projects(): void
    {
        $user = User::factory()->create();
        $team = Team::factory()->create();
        $project = Project::factory()->create(['user_id' => $user->id, 'team_id' => $team->id]);
        $teamRole = Role::create(['name' => 'member']);
        $projectRole = Role::create(['name' => 'visitor']);

        $user->projects()->attach($project->id, ['role_id' => $projectRole->id]);
        $user->teams()->attach($team->id, ['role_id' => $teamRole->id]);

        $user->load('projects');
        $projects = $user->projects;

        $this->assertCount(1, $projects);
        $this->assertTrue($projects->contains($project));
        $this->assertEquals($projectRole->id, $projects->first()->pivot->role_id);

    }

    public function test_user_can_access_team_role_independently(): void
    {
        $user = User::factory()->create();
        $team = Team::factory()->create();
        $project = Project::factory()->create(['team_id' => $team->id, 'user_id' => $user->id]);
        $role = Role::create(['name' => 'admin']);

        $user->teams()->attach($team->id, ['role_id' => $role->id]);
        $user->projects()->attach($project->id, ['role_id' => $role->id]);

        $user->unsetRelation('teams');

        $this->assertCount(1, $user->teams);
        $this->assertInstanceOf(Team::class, $user->teams->first());
        $this->assertEquals($role->id, $user->teams->first()->pivot->role_id);
    }

    public function test_project_can_list_its_assigned_users_with_roles(): void
    {
        $team = Team::factory()->create();
        $leader = User::factory()->create();
        $users = User::factory()->count(2)->create();
        $project = Project::factory()->create(['team_id' => $team->id, 'user_id' => $leader->id]);
        $role = Role::create(['name' => 'contributor']);

        foreach ($users as $user) {
            $project->users()->attach($user->id, ['role_id' => $role->id]);
        }

        $this->assertCount(2, $project->users);
        $this->assertInstanceOf(User::class, $project->users->first());

        $this->assertEquals($role->id, $project->users->first()->pivot->role_id);
    }

    public function test_creator_is_distinct_from_assigned_members(): void
    {
        $team = Team::factory()->create();
        $creator = User::factory()->create();
        $member = User::factory()->create();
        $project = Project::factory()->create(['user_id' => $creator->id, 'team_id' => $team->id]);

        $role = Role::firstOrCreate(['name' => 'editor']);
        $project->users()->attach($member->id, ['role_id' => $role->id]);

        $projectCreator = $project->creator;

        $this->assertInstanceOf(User::class, $projectCreator);
        $this->assertEquals($creator->id, $projectCreator->id);
        $this->assertNotEquals($member->id, $projectCreator->id);
    }

    public function test_tasks_are_strictly_isolated_by_their_objectives(): void
    {
        $team = Team::factory()->create();
        $user = User::factory()->create();
        $project = Project::factory()->create(['team_id' => $team->id, 'user_id' => $user->id]);

        $objectiveA = Objective::factory()->create(['project_id' => $project->id]);
        $objectiveB = Objective::factory()->create(['project_id' => $project->id]);

        $tasksForA = Task::factory()->count(3)->create(['objective_id' => $objectiveA->id]);
        $tasksForB = Task::factory()->count(2)->create(['objective_id' => $objectiveB->id]);

        $resultA = $objectiveA->tasks;
        $resultB = $objectiveB->tasks;

        $this->assertCount(3, $resultA);
        $this->assertCount(2, $resultB);

        foreach ($tasksForB as $taskB) {
            $this->assertFalse(
                $resultA->contains($taskB));
        }
    }

    public function test_objectives_are_strictly_isolated_by_their_projects(): void{
        $team = Team ::factory()->create();
        $user = User::factory()->create();
        $projectA = Project::factory()->create(['team_id' => $team->id, 'user_id' => $user->id]);
        $projectB = Project::factory()->create(['team_id' => $team->id, 'user_id' => $user->id]);
        $objectivesForA = Objective::factory()->count(2)->create(['project_id' => $projectA->id]);
        $objectivesForB = Objective::factory()->count(3)->create(['project_id' => $projectB->id]);

        $resultA = $projectA->objectives;
        $resultB = $projectB->objectives;

        $this->assertCount(2, $resultA);
        $this->assertCount(3, $resultB);

        foreach($objectivesForB as $objectiveB){
            $this->assertFalse(
                $resultA->contains($objectiveB)
            );
        }
    }
}
