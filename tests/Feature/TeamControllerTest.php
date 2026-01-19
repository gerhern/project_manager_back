<?php

namespace Tests\Feature;

use App\Models\Team;
use App\Models\User;
use Database\Seeders\RolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class TeamControllerTest extends TestCase
{
    use RefreshDatabase;

    private Role $adminRole;
    private Role $memberRole;

    protected function setUp(): void {
        parent::setUp();

        $this->seed(RolesSeeder::class);
        $this->adminRole = Role::where('name', 'Admin')->first();
        $this->memberRole = Role::where('name', 'Member')->first();
    }

    public function test_user_can_only_see_their_teams(): void
    {
        $teamAdmin = Team::factory()->create();
        $teamMember = Team::factory()->create();
        Team::factory()->create();
        $user = User::factory()->create();

        $user->teams()->attach($teamAdmin->id, ['role_id' => $this->adminRole->id]);
        $user->teams()->attach($teamMember->id, ['role_id' => $this->memberRole->id]);

        $response = $this->actingAs($user)
            ->getJson(route('teams.index'));

        $response->assertJsonStructure([
            'success',
            'data',
            'message'
        ])->assertJson([
                    'success' => true,
                    'message' => 'Data Retrieved Successfuly'
                ])
            ->assertStatus(200);

        $this->assertEquals($teamAdmin->name, $response->json('data.0.name'));

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
            'role_id' => $this->adminRole->id
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
        $otherUser = User::factory()->create();
        $team = Team::factory()->create(['name' => 'first name']);

        $adminUser->teams()->attach($team->id, ['role_id' => $this->adminRole->id]);
        $otherUser->teams()->attach($team->id, ['role_id' => $this->memberRole->id]);

        $this->actingAs($adminUser)
            ->putJson(route('teams.update', $team), ['name' => 'new name'])
            ->assertJsonStructure(['success', 'data', 'message'])
            ->assertJson(['success' => true, 'message' => 'Team updated successfully.'])
            ->assertStatus(200);

        $this->assertDatabaseHas('teams', ['name' => 'new name']);
    }

    public function test_invalid_user_can_not_update_team():void {
        $adminUser = User::factory()->create();
        $otherUser = User::factory()->create();
        $team = Team::factory()->create(['name' => 'first name']);

        $adminUser->teams()->attach($team->id, ['role_id' => $this->adminRole->id]);
        $otherUser->teams()->attach($team->id, ['role_id' => $this->memberRole->id]);

        $this->actingAs($otherUser)
            ->putJson(route('teams.update', $team), ['name' => 'third name'])
            ->assertJsonStructure(['success', 'message'])
            ->assertJson(['success' => false])
            ->assertStatus(403);

        $this->assertDatabaseMissing('teams', ['name' => 'third name']);
    }



}
