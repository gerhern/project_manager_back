<?php

namespace Tests\Feature;

use App\Models\User;
use App\Traits\SetTestingData;
use Database\Seeders\RolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ObjectiveControllerTest extends TestCase
{
    use RefreshDatabase, SetTestingData;

    protected function setUp(): void {
        parent::setUp();
        $this->seed(RolesSeeder::class);
    }


    public function test_index_show_objectives(): void {
        [$user, $team, $projectA, $objective1] = $this->createObjective();
        $this->createObjective([], $user, $team, $projectA);
        $this->createObjective([], $user, $team, $projectA);
        $userA = User::factory()->create();
        $this->addUserToProject($projectA, $userA);

        [$userB, $teamB, $projectB, $objectiveB] = $this->createObjective([], $user, $team);

        $this->actingAs($userA)
            ->getJson(route('projects.objectives.index', $projectA)) // Enviamos el modelo o ID
            ->assertStatus(200)
            ->assertJsonCount(3, 'data') 
            ->assertJsonFragment(['id' => $objective1->id])
            ->assertJsonMissing(['id' => $objectiveB->id]);
    }

    public function test_only_valid_user_can_create_new_objective(): void {
        [$owner, $team, $project] = $this->createProject();
        $manager = User::factory()->create();
        $user = User::factory()->create();
        $viewer = User::factory()->create();
        $stranger = User::factory()->create();

        $this->addUserToProject($project, $manager);
        $this->addUserToProject($project, $user, 'User');
        $this->addUserToProject($project, $viewer, 'Viewer');
        // $this->addUserToProject($project, $owner);

        $this->actingAs($owner)
            ->postJson(route('projects.objectives.store', $project), [
                'title' => 'owner objective', 
            ])->assertJson(['success' => true, 'message' => 'Objective created successfully']);
        
        $this->assertDatabaseHas('objectives', [
            'project_id' => $project->id, 
            'title' => 'owner objective'
        ]);

        $this->actingAs($manager)
            ->postJson(route('projects.objectives.store', $project), [
                'title' => 'manager objective',
            ])->assertJson(['success' => true, 'message' => 'Objective created successfully']);
        
        $this->assertDatabaseHas('objectives', [
                'project_id' => $project->id,
                'title' => 'manager objective'
            ]);
        
        $this->actingAs($user)
            ->postJson(route('projects.objectives.store', $project), [
                'title' => 'user objective',
                'project_id' => $project->id
            ])->assertJson(['success' => true, 'message' => 'Objective created successfully']);

        $this->assertDatabaseHas('objectives', ['project_id' => $project->id, 'title' => 'user objective']);

        $this->actingAs($viewer)
            ->postJson(route('projects.objectives.store', $project), [
                'title' => 'viewer objective',
                'project_id' => $project->id
            ])->assertJson(['success' => false, 'message' => 'This action is unauthorized, PPCO']);
        
        $this->assertDatabaseMissing('objectives', ['project_id' => $project->id, 'title' => 'viewer objective']);

        $this->actingAs($stranger)
            ->postJson(route('projects.objectives.store', $project), [
                'title' => 'stranger objective',
                'project_id' => $project->id
            ])->assertJson(['success' => false, 'message' => 'This action is unauthorized, PPCO']);

        $this->assertDatabaseMissing('objectives', ['project_id' => $project->id, 'title' => 'stranger objective']);
    }

    public function test_only_valid_user_can_update_objective(): void {
        [$owner, $team, $project, $objective] = $this->createObjective();
        $manager = User::factory()->create();
        $user = User::factory()->create();
        $viewer = User::factory()->create();
        $stranger = User::factory()->create();

        $this->addUserToProject($project, $manager);
        $this->addUserToProject($project, $user, 'User');
        $this->addUserToProject($project, $viewer, 'Viewer');

        $this->actingAs($owner)
            ->putJson(route('projects.objectives.update', [$project, $objective]), ['title' => 'owner name'])
            ->assertJson(['success' => true, 'message' => 'Objective updated successfully']);
        
        $this->assertDatabaseHas('objectives', ['id' => $objective->id, 'title' => 'owner name']);

        $this->actingAs($manager)
            ->putJson(route('projects.objectives.update', [$project, $objective]), ['title' => 'manager name'])
            ->assertJson(['success' => true, 'message' => 'Objective updated successfully']);
        
        $this->assertDatabaseHas('objectives', ['id' => $objective->id, 'title' => 'manager name']);

        $this->actingAs($user)
            ->putJson(route('projects.objectives.update', [$project, $objective]), ['title' => 'user name'])
            ->assertJson(['success' => true, 'message' => 'Objective updated successfully']);
        
        $this->assertDatabaseHas('objectives', ['id' => $objective->id, 'title' => 'user name']);

        $this->actingAs($stranger)
            ->putJson(route('projects.objectives.update', [$project, $objective]), [
                'title' => 'stranger objective',
            ])->assertJson(['success' => false, 'message' => 'This action is unauthorized, OPUO']);

        $this->assertDatabaseMissing('objectives', ['project_id' => $project->id, 'title' => 'stranger objective']);
    }

    public function test_show_works(): void {
        [$owner, $team, $project, $objective] = $this->createObjective();
        $viewer = User::factory()->create();
        $stranger = User::factory()->create();

        $this->addUserToProject($project, $viewer, 'Viewer');

        $this->actingAs($owner)
            ->getJson(route('projects.objectives.show', [$project, $objective]))
            ->assertJson(['success' => true, 'message' => 'Objective retrieved successfully'])
            ->assertJsonFragment(['id' => $objective->id]);

        
        $this->actingAs($viewer)
            ->getJson(route('projects.objectives.show', [$project, $objective]))
            ->assertJsonFragment(['id' => $objective->id])
            ->assertJson(['success' => true, 'message' => 'Objective retrieved successfully']);

        $this->actingAs($stranger)
            ->getJson(route('projects.objectives.show', [$project, $objective]))
            ->assertJson(['success' => false, 'message' => 'This action is unauthorized, PPVP']);
    }
}
