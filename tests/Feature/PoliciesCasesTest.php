<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

use App\Models\{User, Team, Project, ProjectDispute, Objective, Task};
use Spatie\Permission\Models\{Role, Permission};
use Database\Seeders\RolesSeeder;
use App\Enums\DisputeStatus;

class PoliciesCasesTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_leader_can_resolve_disputes(){
        $projectOwner = User::factory()->create();
        $otherUser = User::factory()->create();

        $team = Team::factory()->create();
        $project = Project::factory()->create(['team_id' => $team->id, 'user_id' => $projectOwner->id]);

        $dispute = ProjectDispute::factory()->create(['project_id' => $project->id, 'user_id' => $otherUser->id]);

        $this->actingAs($otherUser)
            ->putJson(route('dispute.resolve', $dispute), ['status' => DisputeStatus::Accepted])
            ->assertStatus(403);

        $this->actingAs($projectOwner)
            ->putJson(route('dispute.resolve', $dispute), ['status' => DisputeStatus::Accepted])
            ->assertStatus(200);
    }

    public function test_manager_and_user_can_update_status(){
        
        $this->seed(RolesSeeder::class);

        $manager = User::factory()->create();
        $manager->assignRole('Manager');
        $employee = User::factory()->create();
        $employee->assignRole('User');
        $employeeB = User::factory()->create();
        $employeeB->assignRole('User');
        
        $team = Team::factory()->create();
        $project = Project::factory()->create(['team_id' => $team->id, 'user_id' => $manager->id]);
        $objective = Objective::factory()->create(['project_id' => $project->id]);
        $task = Task::factory()->create(['objective_id' => $objective->id, 'user_id' => $employee->id]);


        $this->actingAs($employee)
            ->putJson(route('task.updateStatus', $task), ['status' => 'Canceled'])
            ->assertStatus(403);

        $this->actingAs($manager)
            ->putJson(route('task.updateStatus', $task), ['status' => 'Canceled'])
            ->assertStatus(418);

        $this->actingAs($employee)
            ->putJson(route('task.updateStatus', $task), ['status' => 'Completed'])
            ->assertStatus(418);

        $this->actingAs($employeeB)
            ->putJson(route('task.updateStatus', $task), ['status' => 'Completed'])
            ->assertStatus(403);
    }
}
