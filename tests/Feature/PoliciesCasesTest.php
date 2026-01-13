<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

use App\Models\{User, Team, Project, ProjectDispute, Objective, Task};
use Spatie\Permission\Models\Role;
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
            ->assertStatus(418);
    }

    public function test_only_manager_can_cancel_tasks(){
        $team = Team::factory()->create();
        $manager = User::factory()->create();
        Role::create(['name' => 'manager']);
        Role::create(['name' => 'employee']);
        $manager->assignRole('manager');
        $project = Project::factory()->create(['team_id' => $team->id, 'user_id' => $manager->id]);
        $objective = Objective::factory()->create(['project_id' => $project->id]);
        $task = Task::factory()->create(['objective_id' => $objective->id, 'user_id' => $manager->id]);
        $employee = User::factory()->create();
        $employee->assignRole('employee');

        $objective->refresh();
        $task->refresh();
        $project->refresh();

        dd($task->status, $objective->status, $project->status);

        $this->actingAs($employee)
            ->putJson(route('task.update', $task), ['status' => 'cancelled'])
            ->assertStatus(403);

        $this->actingAs($manager)
            ->putJson(route('task.update', $task), ['status' => 'cancelled'])
            ->assertStatus(418);
    }
}
