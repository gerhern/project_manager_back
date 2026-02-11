<?php

namespace Tests\Feature;

use App\Enums\RoleList;
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
        Team::factory()->create();
        $user = User::factory()->create();

        $this->addUserToTeam($teamAdmin, $user, 'Admin');
        $this->addUserToTeam($teamMember, $user, 'Member');

        $response = $this->actingAs($user)
            ->getJson(route('teams.index'))
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJson([
                'success' => true,
                'message' => 'Data retrieved successfuly'
            ])->assertJsonFragment(['id' => $teamAdmin->id])
            ->assertJsonFragment(['id' => $teamMember->id]);

        $teamIds = collect($response->json('data'))->pluck('id')->toArray();
        $this->assertCount(2, $teamIds);
    }

    public function test_user_can_create_a_team_and_become_admin(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->postJson(route('teams.store'), ['name' => 'my new Team'])
            ->assertStatus(201)
            ->assertJson(['success' => true, 'message' => 'Team created successfully']);

        $this->assertDatabaseHas('teams', ['name' => 'my new Team']);

        $team = Team::where('name', 'my new Team')->first();

        $this->assertDatabaseHas('memberships', [
            'user_id' => $user->id,
            'model_id' => $team->id,
            'model_type' => Team::class,
            'role_id' => $this->getRole(RoleList::Owner->value)->id
        ]);
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
        [$adminUser, $team] = $this->createTeam(['name' => 'first name']);

        $this->addUserToTeam($team, $adminUser, 'Admin');

        $this->actingAs($adminUser)
            ->putJson(route('teams.update', $team), ['name' => 'new name'])
            ->assertJson(['success' => true, 'message' => 'Team updated successfully.'])
            ->assertOk();

        $this->assertDatabaseHas('teams', ['name' => 'new name']);
    }

    public function test_invalid_user_can_not_update_team():void {
        [$otherUser, $team] = $this->createTeam(['name' => 'first name']);

        $this->addUserToTeam($team, $otherUser, 'Member');

        $this->actingAs($otherUser)
            ->putJson(route('teams.update', $team), ['name' => 'third name'])
            ->assertJson(['success' => false, 'message' => 'This action is unauthorized, TPUT'])
            ->assertForbidden();

        $this->assertDatabaseMissing('teams', ['name' => 'third name']);
    }

    public function test_only_owner_can_update_status(): void {
        [$owner, $team] = $this->createTeam();
        $stranger = User::factory()->create();
        $this->addUserToTeam($team, $owner, RoleList::Owner->value);

        $this->actingAs($stranger)
            ->deleteJson(route('teams.inactive', $team))
            ->assertForbidden()
            ->assertJson(['success' => false, 'message' => 'This action is unauthorized, TPIT']);

        $this->assertDatabaseHas('teams', ['id' => $team->id,'status' => TeamStatus::Active->name]);

        $this->actingAs($owner)
            ->deleteJson(route('teams.inactive', $team))
            ->assertOk()
            ->assertJson(['success' => true, 'message' => 'Team inactivated successfully']);

        $this->assertDatabaseHas('teams', ['id' => $team->id,'status' => TeamStatus::Inactive->name]);
    }

    public function test_show_team_works(): void {
        [$user, $team] = $this->createTeam();

        $this->addUserToTeam($team, $user);
        
        $this->actingAs($user)
            ->getJson(route('teams.show', $team))
            ->assertOk()
            ->assertJson(['success' => true, 'message' => 'Team retrieved successfully', 'data' => ['id' => $team->id]]);

    }

}
