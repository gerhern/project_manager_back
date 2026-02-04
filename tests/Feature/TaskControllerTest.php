<?php

namespace Tests\Feature;

use App\enums\TaskStatus;
use App\Models\User;
use App\Traits\SetTestingData;
use Carbon\Carbon;
use Database\Seeders\RolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class TaskControllerTest extends TestCase
{
    use RefreshDatabase, SetTestingData;

    public function test_index_show_task_to_user(): void {
        $this->seed(RolesSeeder::class);
        
        [$owner, $team, $project, $objective, $task] = $this->createTask();
        [,,,, $taskB] = $this->createTask([], null, $team, $project, $objective);
        [,,,, $taskC] = $this->createTask([], null, $team, $project);
        
        $user = User::factory()->create();
        $stranger = User::factory()->create();

        $this->addUserToProject($project, $user, 'User');

        $this->actingAs($stranger)
            ->getJson(route('projects.objectives.tasks.index',[$project, $objective]))
            ->assertForbidden()
            ->assertJson(['success' => false, 'message' => 'This action is unauthorized, TKPITK']);

        $this->actingAs($user)
            ->getJson(route('projects.objectives.tasks.index', [$project, $objective]))
            ->assertOk()
            ->assertJson(['success' => true, 'message' => 'Tasks retrieved successfully'])
            ->assertJsonCount(2, 'data')
            ->assertJsonFragment(['id' => $task->id])
            ->assertJsonFragment(['id' => $taskB->id])
            ->assertJsonMissing(['id' => $taskC->id]);

        $this->actingAs($owner)
            ->getJson(route('projects.objectives.tasks.index', [$project, $objective]))
            ->assertOk()
            ->assertJson(['success' => true, 'message' => 'Tasks retrieved successfully'])
            ->assertJsonCount(2, 'data')
            ->assertJsonFragment(['id' => $task->id])
            ->assertJsonFragment(['id' => $taskB->id])
            ->assertJsonMissing(['id' => $taskC->id]);
    }

    public function test_store_save_tasks(): void {
        $this->seed(RolesSeeder::class);
        [$owner, $team, $project, $objective] = $this->createObjective();
        $user = User::factory()->create();
        $stranger = User::factory()->create();

        $this->addUserToProject($project, $user, 'User');

        $this->actingAs($stranger)
            ->postJson(
                route('projects.objectives.tasks.store',[$project, $objective]), 
                [
                        'title' => 'stranger name',
                        'due_date'  => Carbon::today()->addDays(2)->toDateString()
                    ]
            )
            ->assertForbidden()
            ->assertJson(['success' => false, 'message' => 'This action is unauthorized, TKPCTK']);

        $this->assertDatabaseMissing('tasks', ['title' => 'stranger name']);

        $this->actingAs($user)
            ->postJson(
                route('projects.objectives.tasks.store', [$project, $objective]),
                [
                        'title'     => 'user name',
                        'due_date'  => Carbon::today()->addDays(2)->toDateString()
                    ]
            )
            ->assertStatus(201)
            ->assertJson(['success' => true, 'message' => 'Task created successfully']);

        $this->assertDatabaseHas('tasks', ['title' => 'user name', 'objective_id' => $objective->id,]);

        $this->actingAs($owner)
            ->postJson(
                route('projects.objectives.tasks.store', [$project, $objective]),
                [
                        'title' => 'owner name',
                        'due_date'  => Carbon::today()->addDays(2)->toDateString()
                    ]
            )
            ->assertStatus(201)
            ->assertJson(['success' => true, 'message' => 'Task created successfully']);

        $this->assertDatabaseHas('tasks', ['title' => 'owner name', 'objective_id' => $objective->id,]);
    }

    public function test_show_task_works(): void {
        $this->seed(RolesSeeder::class);
        [$owner, $team, $project, $objective, $task] = $this->createTask();
        $user = User::factory()->create();
        $stranger = User::factory()->create();
        [,,,,$taskB] = $this->createTask([], $owner, $team, $project, $objective);
        $this->addUserToProject($project, $owner);
        $this->addUserToProject($project, $user, 'Viewer');

        $this->actingAs($owner)
            ->getJson(route('projects.objectives.tasks.show', [$project, $objective, $task]))
            ->assertOk()
            ->assertJson(['success' => true, 'message' => 'Task retrieved successfully'])
            ->assertJsonFragment(['id' => $task->id])
            ->assertJsonMissing(['id' => $taskB->id]);

        $this->actingAs($user)
            ->getJson(route('projects.objectives.tasks.show', [$project, $objective, $task]))
            ->assertOk()
            ->assertJson(['success' => true, 'message' => 'Task retrieved successfully'])
            ->assertJsonFragment(['id' => $task->id])
            ->assertJsonMissing(['id' => $taskB->id]);

        $this->actingAs($stranger)
            ->getJson(route('projects.objectives.tasks.show', [$project, $objective, $task]))
            ->assertForbidden()
            ->assertJson(['success' => false, 'message' => 'This action is unauthorized, TKPSTK']);
    }

    public function test_valid_user_can_update_task(): void {
        $this->seed(RolesSeeder::class);
        [$owner, $team, $project, $objective, $task] = $this->createTask();
        $stranger = User::factory()->create();
        $viewer = User::factory()->create();
        $user = User::factory()->create();

        $this->addUserToProject($project, $viewer, 'Viewer');
        $this->addUserToProject($project, $user, 'User');

        $this->actingAs($stranger)
            ->putJson(
                route('projects.objectives.tasks.update', [$project, $objective, $task]),
                ['title' => 'stranger title', 'due_date'  => Carbon::today()->addDays(2)->toDateString()]
            )->assertForbidden()
            ->assertJson(['success' => false, 'message' => 'This action is unauthorized, TKPUTK']);

        $this->assertDatabaseMissing('tasks', ['title' => 'stranger title']);

        $this->actingAs($viewer)
            ->putJson(
                route('projects.objectives.tasks.update', [$project, $objective, $task]),
                ['title' => 'viewer title', 'due_date'  => Carbon::today()->addDays(2)->toDateString()]
            )->assertForbidden()
            ->assertJson(['success' => false, 'message' => 'This action is unauthorized, TKPUTK']);
        
        $this->assertDatabaseMissing('tasks', ['title' => 'viewer title']);

        $this->actingAs($user)
            ->putJson(
                route('projects.objectives.tasks.update', [$project, $objective, $task]),
                ['title' => 'user title', 'due_date'  => Carbon::today()->addDays(2)->toDateString()]        
            )
            ->assertOk()
            ->assertJson(['success' => true, 'message' => 'Task updated successfully']);

        $this->assertDatabaseHas('tasks', ['title' => 'user title']);

        $this->actingAs($owner)
            ->putJson(
                route('projects.objectives.tasks.update', [$project, $objective, $task]),
                ['title' => 'owner title', 'due_date'  => Carbon::today()->addDays(2)->toDateString()]        
            )
            ->assertOk()
            ->assertJson(['success' => true, 'message' => 'Task updated successfully']);

        $this->assertDatabaseHas('tasks', ['title' => 'owner title']);
    }

    public function test_task_can_be_canceled(): void {
        $this->seed(RolesSeeder::class);
        [$owner,, $project, $objective, $task] = $this->createTask();
        $viewer = User::factory()->create();
        $stranger = User::factory()->create();
        $user = User::factory()->create();

        $this->addUserToProject($project, $viewer, 'Viewer');
        $this->addUserToProject($project, $user, 'User');

        $this->actingAs($stranger)
            ->deleteJson(
                route('projects.objectives.tasks.delete', [$project, $objective, $task]),
                ['status' => TaskStatus::Canceled->name]
            )->assertForbidden()
            ->assertJson(['success' => false, 'message' => 'This action is unauthorized, TKPDTK']);

        $this->assertDatabaseMissing('tasks', ['status' => TaskStatus::Canceled]);

        $this->actingAs($viewer)
            ->deleteJson(
                route('projects.objectives.tasks.delete', [$project, $objective, $task]),
                ['status' => TaskStatus::Canceled->name]
            )->assertForbidden()
            ->assertJson(['success' => false, 'message' => 'This action is unauthorized, TKPDTK']);

        $this->assertDatabaseMissing('tasks', ['status' => TaskStatus::Canceled]);

        $this->actingAs($user)
            ->deleteJson(
                route('projects.objectives.tasks.delete', [$project, $objective, $task]),
                ['status' => TaskStatus::Canceled->name]
            )->assertOk()
            ->assertJson(['success' => true, 'message' => 'Task canceled successfully']);

        $this->assertDatabaseHas('tasks', ['id' => $task->id, 'status' => TaskStatus::Canceled]);

        $task->update(['status' => TaskStatus::Assigned]);

        $this->actingAs($owner)
            ->deleteJson(
                route('projects.objectives.tasks.delete', [$project, $objective, $task]),
                ['status' => TaskStatus::Canceled->name]
            )->assertOk()
            ->assertJson(['success' => true, 'message' => 'Task canceled successfully']);

        $this->assertDatabaseHas('tasks', ['id' => $task->id, 'status' => TaskStatus::Canceled]);
    }

    public function test_task_status_can_be_updated(): void {
        $this->seed(RolesSeeder::class);
        [$owner,, $project, $objective, $task] = $this->createTask();

        $stranger = User::factory()->create();
        $viewer = User::factory()->create();
        $user = User::factory()->create();

        $this->addUserToProject($project, $viewer, 'Viewer');
        $this->addUserToProject($project, $user, 'User');

        $this->actingAs($stranger)
            ->putJson(
                route('projects.objectives.tasks.status', [$project, $objective, $task]),
                ['status' => TaskStatus::Assigned->name])
            ->assertForbidden()
            ->assertJson(['success' => false, 'message' => 'This action is unauthorized, TKPUSTK']);

        $this->assertDatabaseMissing('tasks', ['status' => TaskStatus::Assigned->name]);

        $this->actingAs($viewer)
            ->putJson(
                route('projects.objectives.tasks.status', [$project, $objective, $task]),
                ['status' => TaskStatus::Assigned->name])
            ->assertForbidden()
            ->assertJson(['success' => false, 'message' => 'This action is unauthorized, TKPUSTK']);

        $this->assertDatabaseMissing('tasks', ['status' => TaskStatus::Assigned->name]);

        $this->actingAs($user)
            ->putJson(
                route('projects.objectives.tasks.status',[$project, $objective, $task]),
                ['status' => TaskStatus::Assigned->name])
            ->assertOk()
            ->assertJson(['success' => true, 'message' => 'Task status updated successfully']);
        $this->assertDatabaseHas('tasks', ['status' => TaskStatus::Assigned]);

        $this->actingAs($owner)
            ->putJson(
                route('projects.objectives.tasks.status',[$project, $objective, $task]),
                ['status' => TaskStatus::InProgress->name])
            ->assertOk()
            ->assertJson(['success' => true, 'message' => 'Task status updated successfully']);
        $this->assertDatabaseHas('tasks', ['status' => TaskStatus::InProgress->name]);

         $this->actingAs($user)
            ->putJson(
                route('projects.objectives.tasks.status',[$project, $objective, $task]),
                ['status' => TaskStatus::Canceled->name])
            ->assertForbidden()
            ->assertJson(['success' => false, 'message' => 'This action is unauthorized, TKPUSTK']);
        $this->assertDatabaseMissing('tasks', ['status' => TaskStatus::Canceled->name]);
    }
}
