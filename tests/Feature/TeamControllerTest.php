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
    public function test_user_can_only_see_their_teams(): void{
        $this->seed(RolesSeeder::class);

        $adminRole = Role::where('name', 'Admin')
            ->first()
            ->id;

        $memberRole = Role::where('name', 'Member')
            ->first()
            ->id;

        $teamAdmin = Team::factory()->create();
        $teamMember = Team::factory()->create();
        Team::factory()->create();
        $user = User::factory()->create();
        
        $user->teams()->attach($teamAdmin->id, ['role_id' => $adminRole]);
        $user->teams()->attach($teamMember->id, ['role_id'=> $memberRole]);

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

    
}
