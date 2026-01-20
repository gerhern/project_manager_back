<?php

namespace Tests\Feature;

use App\Enums\TeamStatus;
use App\Models\Team;
use App\Models\User;
use App\Traits\SetTestingData;
use Database\Seeders\RolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class TeamControllerTest extends TestCase
{
    use RefreshDatabase, SetTestingData ;

    protected function setUp(): void {
        parent::setUp();
        $this->seed(RolesSeeder::class);
    }

    public function test_user_can_only_see_their_teams(): void
    {
        $teamAdmin = Team::factory()->create();
        $teamMember = Team::factory()->create();
        Team::factory(2)->create();
        $user = User::factory()->create();

        $this->addUserToTeam($teamAdmin, $user, 'Admin');
        $this->addUserToTeam($teamMember, $user, 'Member');

        $response = $this->actingAs($user)
            ->getJson(route('teams.index'));

        $response = $this->actingAs($user)
        ->getJson(route('teams.index'));

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data')
            ->assertJson([
                'success' => true,
                'message' => 'Data Retrieved Successfuly'
            ]);
        $teamIds = collect($response->json('data'))->pluck('id')->toArray();
        $this->assertContains($teamAdmin->id, $teamIds);
        $this->assertContains($teamMember->id, $teamIds);

        $this->assertCount(2, $teamIds);
    }

    public function test_user_can_create_a_team_and_become_admin(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->postJson(route('teams.store'), ['name' => 'my new Team']);

        $response->assertStatus(201)
            ->assertJson(['success' => true, 'message' => 'Team created successfully']);

        $this->assertDatabaseHas('teams', ['name' => 'my new Team']);

        $team = Team::where('name', 'my new Team')->first();

        $this->assertDatabaseHas('memberships', [
            'user_id' => $user->id,
            'model_id' => $team->id,
            'model_type' => Team::class,
            'role_id' => $this->getRole('Admin')->id
        ]);
    }

    public function test_team_creation_fails_validation_with_standardized_format()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->postJson(route('teams.store'), [
                'name' => 'ab',
            ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Data validation errors',
            ])
            ->assertJsonStructure(['data' => ['name']]);
    }

    public function test_it_rolls_back_everything_if_membership_fails()
    {
        $user = User::factory()->create();
        Team::created(function () {
            throw new \Exception("Simulated Failure After Team Creation");
        });
        $response = $this->actingAs($user)->postJson(route('teams.store'), [
            'name' => 'Atomic Team Test'
        ]);
        $response->assertStatus(500);
        $this->assertDatabaseMissing('teams', ['name' => 'Atomic Team Test']);
    }

    public function test_valid_user_can_update_team_data(): void
    {
        $adminUser = User::factory()->create()->assignRole('Admin');
        $team = Team::factory()->create(['name' => 'first name']);

        $this->addUserToTeam($team, $adminUser, 'Admin');

        $this->actingAs($adminUser)
            ->putJson(route('teams.update', $team), ['name' => 'new name'])
            ->assertJsonStructure(['success', 'data', 'message'])
            ->assertJson(['success' => true, 'message' => 'Team updated successfully.'])
            ->assertStatus(200);

        $this->assertDatabaseHas('teams', ['name' => 'new name']);
    }

    public function test_invalid_user_can_not_update_team():void {
        $otherUser = User::factory()->create();
        $team = Team::factory()->create(['name' => 'first name']);

        $this->addUserToTeam($team, $otherUser, 'Member');

        $this->actingAs($otherUser)
            ->putJson(route('teams.update', $team), ['name' => 'third name'])
            ->assertJsonStructure(['success', 'message'])
            ->assertJson(['success' => false])
            ->assertStatus(403);

        $this->assertDatabaseMissing('teams', ['name' => 'third name']);
    }

    public function test_only_owner_can_update_status(): void {
        [$owner, $team] = $this->createTeam();
        $stranger = User::factory()->create();
        $this->addUserToTeam($team, $owner, 'Owner');

        $this->actingAs($stranger)
            ->deleteJson(route('teams.inactive', $team))
            ->assertStatus(403)
            ->assertJsonStructure(['success', 'message'])
            ->assertJson(['success' => false]);

        $this->assertDatabaseHas('teams', ['id' => $team->id,'status' => TeamStatus::Active->name]);

        $this->actingAs($owner)
            ->deleteJson(route('teams.inactive', $team))
            ->assertStatus(200)
            ->assertJsonStructure(['success', 'data', 'message'])
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('teams', ['id' => $team->id,'status' => TeamStatus::Inactive->name]);
    }

}
