<?php

namespace Tests\Feature;

use App\Enums\RoleList;
use App\Models\Task;
use App\Models\Team;
use App\Models\User;
use App\Models\Objective;
use App\Traits\SetTestingData;
use Database\Seeders\RolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckRelatedModelTest extends TestCase
{
    use RefreshDatabase, SetTestingData;

    public function test_user_projects_only_returns_projects(): void
    {
        $this->seed(RolesSeeder::class);

        [$user, $team, $project] = $this->createProject();
        $projectRole = $this->getRole(RoleList::Viewer->value);

        $this->addUserToProject($project, $user, RoleList::Viewer->value);
        $this->addUserToTeam($team, $user, RoleList::Member->value);

        $user->load('projects');
        $projects = $user->projects;

        $this->assertCount(1, $projects);
        $this->assertTrue($projects->contains($project));
        $this->assertEquals($projectRole->id, $projects->first()->pivot->role_id);

    }

    public function test_user_can_access_team_role_independently(): void
    {
        $this->seed(RolesSeeder::class);
        [$user, $team, $project] =$this->createProject();
        $role = $this->getRole(RoleList::Admin->value);

        $this->addUserToTeam($team, $user, RoleList::Admin->value);
        $this->addUserToProject($project, $user);

        $user->unsetRelation('teams');

        $this->assertCount(1, $user->teams);
        $this->assertInstanceOf(Team::class, $user->teams->first());
        $this->assertEquals($role->id, $user->teams->first()->pivot->role_id);
    }

    public function test_project_can_list_its_assigned_users_with_roles(): void
    {
        $this->seed(RolesSeeder::class);

        $users = User::factory()->count(2)->create();

        [,, $project] = $this->createProject();
        $role = $this->getRole(RoleList::User->value);

        foreach ($users as $user) {
            $this->addUserToProject($project, $user, RoleList::User->value);
        }

        $this->assertCount(2, $project->users);
        $this->assertInstanceOf(User::class, $project->users->first());
        $this->assertEquals($role->id, $project->users->first()->pivot->role_id);
    }

    public function test_creator_is_distinct_from_assigned_members(): void
    {
        $this->seed(RolesSeeder::class);
        $member = User::factory()->create();
        [$creator,, $project] = $this->createProject();

        $this->addUserToProject($project, $creator);

        $projectCreator = $project->creator;

        $this->assertInstanceOf(User::class, $projectCreator);
        $this->assertEquals($creator->id, $projectCreator->id);
        $this->assertNotEquals($member->id, $projectCreator->id);
    }

    public function test_tasks_are_strictly_isolated_by_their_objectives(): void
    {
        [,, $project, $objectiveA] = $this->createObjective();
        $objectiveB = $this->createNewObjetiveOnProject($project);

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

        foreach ($tasksForA as $taskA) {
            $this->assertFalse(
                $resultB->contains($taskA));
        }
    }

    public function test_objectives_are_strictly_isolated_by_their_projects(): void{
        [$user, $team, $projectA] = $this->createProject();
        $projectB = $this->createNewProjectOnTeam($team, $user);
        
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

        foreach($objectivesForA as $objectiveA){
            $this->assertFalse(
                $resultB->contains($objectiveA)
            );
        }
    }
}
