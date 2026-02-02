<?php

namespace Tests\Feature;

use App\Models\User;
use App\Traits\SetTestingData;
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

        $response = $this->actingAs($user)
            ->getJson(route('projects.objectives.tasks.index', [$project, $objective]))
            ->assertOk()
            ->assertJson(['success' => true, 'message' => 'Tasks retrieved successfully'])
            ->assertJsonCount(2, 'data')
            ->assertJsonFragment(['id' => $task->id])
            ->assertJsonFragment(['id' => $taskB->id])
            ->assertJsonMissing(['id' => $taskC->id]);

    }
}
