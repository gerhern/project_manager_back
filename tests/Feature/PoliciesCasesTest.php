<?php

namespace Tests\Feature;

use App\Traits\SetTestingData;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

use App\Models\{User, Team, Project, ProjectDispute, Objective, Task};
use Spatie\Permission\Models\{Role, Permission};
use Database\Seeders\RolesSeeder;
use App\Enums\DisputeStatus;

class PoliciesCasesTest extends TestCase
{
    use RefreshDatabase, SetTestingData;

    public function test_only_leader_can_resolve_disputes(){
        $otherUser = User::factory()->create();
        [$projectOwner,, $project] = $this->createProject();

        $dispute = ProjectDispute::factory()->create(['project_id' => $project->id, 'user_id' => $otherUser->id]);

        $this->actingAs($otherUser)
            ->putJson(route('dispute.resolve', $dispute), ['status' => DisputeStatus::Accepted])
            ->assertStatus(403);

        $this->actingAs($projectOwner)
            ->putJson(route('dispute.resolve', $dispute), ['status' => DisputeStatus::Accepted->name])
            ->assertStatus(200);
    }

    public function test_manager_and_user_can_update_project_status(){
        
        $this->seed(RolesSeeder::class);

        [$manager,,, $objective] = $this->createObjective();
        $manager->assignRole('Manager');
        $employee = User::factory()->create()->assignRole('User');
        $employeeB = User::factory()->create()->assignRole('User');
        
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

    public function test_viewer_cant_update_anything(){
        $this->seed(RolesSeeder::class);

        $viewer = User::factory()->create()->assignRole('Viewer');
        $employee = User::factory()->create()->assignRole('User');
        $manager = User::factory()->create()->assignRole('Manager');

        [,, $project, $objective] = $this->createObjective([], $employee);

        $task = Task::factory()->create(['objective_id' => $objective->id]);

        $this->actingAs($viewer);

            $this->putJson(
                route('projects.objectives.tasks.update', [$project, $objective, $task]),
                 ['title' => 'new title', 'due_date'  => Carbon::today()->addDays(2)->toDateString()])
            ->assertStatus(403)
            ->assertJsonStructure(['success', 'message']);

            $this->putJson(route('projects.objectives.update', [$project, $objective]),['title' => 'Completed'])
            ->assertStatus(403)
            ->assertJsonStructure(['success', 'message']);

            $this->putJson(route('projects.update', $project), ['name' => 'new name'])
            ->assertStatus(403)
            ->assertJsonStructure(['success', 'message']);

        $this->actingAs($employee)
            ->putJson(
                route('projects.objectives.tasks.update', [$project, $objective, $task]),
                ['title' => 'Testing', 'due_date'  => Carbon::today()->addDays(2)->toDateString()])
            ->assertStatus(200)
            ->assertJsonStructure(['success', 'message']);

        $this->actingAs($manager);

            $this->putJson(route('projects.objectives.update', [$project, $objective]), ['title' => 'Testing'])
            ->assertStatus(200)
            ->assertJsonStructure(['success', 'message']);
    }
}
