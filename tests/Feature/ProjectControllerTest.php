<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\User;
use App\Traits\SetTestingData;
use Database\Seeders\RolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ProjectControllerTest extends TestCase
{
    use RefreshDatabase, SetTestingData;
    public function test_projects_index_works(): void{
        $this->seed(RolesSeeder::class);
        [$owner,, $project] = $this->createProject();
        [,, $projectB] = $this->createProject(['user_id' => $owner->id]);
        $member = User::factory()->create();
        $stranger = User::factory()->create();

        $this->addUserToProject($project, $owner);
        $this->addUserToProject($projectB, $owner);
        $this->addUserToProject($project, $member, 'User');

        $onwerResponse = $this->actingAs($owner)
            ->getJson(route('projects.index'))
            ->assertStatus(200)
            ->assertJsonStructure(['success', 'data', 'message'])
            ->assertJson(['success' => true, 'message' => 'Projects retrieved successfully'])
            ->assertJsonCount(2, 'data');

        $ownerIds = collect($onwerResponse->json('data'))->pluck('id')->toArray();
        $this->assertContains($project->id, $ownerIds);
        $this->assertContains($projectB->id, $ownerIds);

        $memberResponse = $this->actingAs($member)
            ->getJson(route('projects.index'))
            ->assertStatus(200)
            ->assertJsonStructure(['success', 'data', 'message'])
            ->assertJson(['success' => true, 'message' => 'Projects retrieved successfully'])
            ->assertJsonCount(1, 'data');

        $memberIds = collect($memberResponse->json('data'))->pluck('id')->toArray();
        $this->assertContains($project->id, $memberIds);

        $this->actingAs($stranger)
            ->getJson(route('projects.index'))
            ->assertStatus(200)
            ->assertJsonStructure(['success', 'data', 'message'])
            ->assertJson(['success' => true, 'message' => 'Projects retrieved successfully'])
            ->assertJsonCount(0, 'data');
    }

    /**
     * Test if a valid user can create a new project on team, acting as admin or member of a team 
     * @return void
     */
    public function test_only_team_admin_can_create_projects(): void {
        $this->seed(RolesSeeder::class);

        [$admin, $team] = $this->createTeam();
        $member = User::factory()->create();

        $this->addUserToTeam($team, $admin);
        $this->addUserToTeam($team, $member, 'Member');

        $this->actingAs($member)
            ->postJson(route('projects.store'), ['name' => 'memberProject', 'team_id' => $team->id])
            ->assertStatus(403);
        
        $this->assertDatabaseMissing('projects', ['name' => 'memberProject']);

        $this->actingAs($admin)
            ->postJson(route('projects.store'), ['name' => 'adminProject', 'team_id' => $team->id])
            ->assertStatus(201)
            ->assertJson(['success' => true, 'message' => 'Project created successfully']);

        $this->assertDatabaseHas('projects', ['name' => 'adminProject']);

        $project = Project::where('name', 'adminProject')->first();

        $this->assertDatabaseHas('memberships', [
            'user_id' => $admin->id,
            'model_id' => $project->id,
            'model_type' => Project::class,
            'role_id' => $this->getRole('Manager')->id
        ]);
    }

    public function test_only_valid_users_can_update_project(): void {
        $this->seed(RolesSeeder::class);

        [$owner, $team, $project] = $this->createProject();
        $user = User::factory()->create();
        $viewer = User::factory()->create();

        $this->addUserToProject($project, $owner);
        $this->addUserToProject($project, $user, 'User');
        $this->addUserToProject($project, $viewer, 'Viewer');

        $this->actingAs($viewer)
            ->putJson(route('projects.update', $project), ['name' => 'viewer name'])
            // ->assertJson(['message' => 'Operation denied'])
            ->assertStatus(403);
        
        $this->actingAs($user)
            ->putJson(route('projects.update', $project), ['name' => 'user name'])
            // ->assertJson(['message' => 'Operation denied'])
            ->assertStatus(403);
        
        $this->actingAs($owner)
            ->putJson(route('projects.update', $project), ['name' => 'owner name'])
            ->assertJson(['success' => true, 'message' => 'Project updated successfully'])
            ->assertJsonStructure(['success', 'data', 'message'])
            ->assertStatus(200);
        
        $this->assertDatabaseHas('projects', [
            'id' => $project->id,
            'name' => 'owner name'
        ]);

    }

 }
