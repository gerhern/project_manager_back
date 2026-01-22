<?php

namespace Tests\Feature;

use App\Enums\DisputeStatus;
use App\Enums\ProjectStatus;
use App\Http\Controllers\ProjectController;
use App\Models\Project;
use App\Models\ProjectDispute;
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

        [$owner, $team, $project] = $this->createProject();
        [,, $projectB] = $this->createProject([], $owner, $team);

        $member = User::factory()->create();
        $stranger = User::factory()->create();

        $this->addUserToProject($project, $owner);
        $this->addUserToProject($project, $member, 'User');
        $this->addUserToProject($projectB, $owner);

        $onwerResponse = $this->actingAs($owner)
            ->getJson(route('projects.index'))
            ->assertJson(['success' => true, 'message' => 'Projects retrieved successfully'])
            ->assertJsonCount(2, 'data');

        $ownerIds = collect($onwerResponse->json('data'))->pluck('id')->toArray();
        $this->assertContains($project->id, $ownerIds);
        $this->assertContains($projectB->id, $ownerIds);

        $memberResponse = $this->actingAs($member)
            ->getJson(route('projects.index'))
            ->assertJson(['success' => true, 'message' => 'Projects retrieved successfully'])
            ->assertJsonCount(1, 'data');

        $memberIds = collect($memberResponse->json('data'))->pluck('id')->toArray();
        $this->assertContains($project->id, $memberIds);

        $this->actingAs($stranger)
            ->getJson(route('projects.index'))
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
            ->assertJson(['success' => false, 'message' => 'This action is unauthorized, TPCP']);
        
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
            'role_id' => $this->getCachedRoleId('Manager')
        ]);
    }

    public function test_only_valid_users_can_update_project(): void {
        $this->seed(RolesSeeder::class);

        [$owner,, $project] = $this->createProject();
        $user = User::factory()->create();
        $viewer = User::factory()->create();

        $this->addUserToProject($project, $owner);
        $this->addUserToProject($project, $user, 'User');
        $this->addUserToProject($project, $viewer, 'Viewer');

        $this->actingAs($viewer)
            ->putJson(route('projects.update', $project), ['name' => 'viewer name'])
            ->assertJson(['success' => false, 'message' => 'This action is unauthorized, PPUP']);
        
        $this->actingAs($user)
            ->putJson(route('projects.update', $project), ['name' => 'user name'])
            ->assertJson(['success' => false, 'message' => 'This action is unauthorized, PPUP']);
        
        $this->actingAs($owner)
            ->putJson(route('projects.update', $project), ['name' => 'owner name'])
            ->assertJson(['success' => true, 'message' => 'Project updated successfully'])
            ->assertJsonStructure(['success', 'data', 'message']);
        
        $this->assertDatabaseHas('projects', [
            'id' => $project->id,
            'name' => 'owner name'
        ]);
    }

    public function test_only_managers_can_try_cancel_project(): void {

        $this->seed(RolesSeeder::class);

        [$owner, $team, $project] = $this->createProject();
        [,,$projectB] = $this->createProject([],$owner, $team);

        $manager = User::factory()->create();
        $this->addUserToProject($project, $manager);
        $user = User::factory()->create();
        $this->addUserToProject($project, $user, 'User');
        $viewer = User::factory()->create();
        $this->addUserToProject($project, $viewer, 'Viewer');

        $this->actingAs($viewer)
            ->deleteJson(route('projects.cancel', $project))
            ->assertJson(['success' => false, 'message' => 'This action is unauthorized, PPCP']);

        $this->actingAs($user)
            ->deleteJson(route('projects.cancel', $project))
            ->assertJson(['success' => false, 'message' => 'This action is unauthorized, PPCP']);
        
        $this->actingAs($manager)
            ->deleteJson(route('projects.cancel', $project))
            ->assertJson(['success' => true, 'message' => 'An open dispute has been created'])
            ->assertStatus(200);
        
        $this->assertDatabaseHas('project_disputes', ['user_id' => $manager->id, 'status' => DisputeStatus::Open->name]);
        $this->assertDatabaseHas('projects', ['status' => ProjectStatus::CancelInProgress->name]);

        $this->actingAs($owner)
            ->deleteJson(route('projects.cancel', $projectB))
            ->assertJson(['success' => true, 'message' => 'The project has been canceled successfully'])
            ->assertStatus(200);

        $this->assertDatabaseHas('projects', ['status' => ProjectStatus::Canceled->name]);
    }

    public function test_owner_update_dispute_project_status(): void {
        [$owner, $team, $project] = $this->createProject();
        $user = User::factory()->create();
        $dispute = $this->createDispute($project, $user);

        $this->actingAs($user)
            ->putJson(route('dispute.resolve', $dispute), ['status' => DisputeStatus::Accepted])
            ->assertJson(['success' => false, 'message' => 'This action is unauthorized, PPDUDS']);

        $this->actingAs($owner)
            ->putJson(route('dispute.resolve', $dispute), ['status' => DisputeStatus::Accepted])
            ->assertJson(['success' => true, 'message' => 'Dispute resolved successfully']);
        
        $this->assertDatabaseHas('project_disputes', ['project_id' => $project->id, 'status' => DisputeStatus::Accepted]);
    }

 }
