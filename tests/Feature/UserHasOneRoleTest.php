<?php

namespace Tests\Feature;

use App\Enums\RoleList;
use App\Traits\SetTestingData;
use Database\Seeders\RolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

use App\Models\User;
use App\Models\Team;
use App\Models\Project;
use Spatie\Permission\Models\Role;

class UserHasOneRoleTest extends TestCase
{
    use RefreshDatabase, SetTestingData;
    
    

    public function test_user_cannot_have_multiple_roles_in_same_project(): void
    {
        $this->seed(RolesSeeder::class);
        $this->expectException(\Illuminate\Database\QueryException::class);

        [$user, $team, $project] = $this->createProject();

        $role1 = $this->getCachedRoleId(RoleList::Member);
        $role2 = $this->getCachedRoleId(RoleList::Admin);

        $user->projects()->attach($project->id, [
            'team_id' => $team->id,
            'role_id' => $role1
        ]);

        // Attempt to attach a second role for the same user and project
        $user->projects()->attach($project->id, [
            'team_id' => $team->id,
            'role_id' => $role2
        ]);
    }

    public function test_it_returns_404_in_standardized_format_when_model_not_found()
    {
        $user = User::factory()->create();

        $this->actingAs($user)->getJson(route('projects.objectives.show', ['100', '100']))
            ->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Resource not found.'
            ]);
    }

    
}
