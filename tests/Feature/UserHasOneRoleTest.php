<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

use App\Models\User;
use App\Models\Team;
use App\Models\Project;
use Spatie\Permission\Models\Role;

class UserHasOneRoleTest extends TestCase
{
    use RefreshDatabase;
    
    

    public function test_user_cannot_have_multiple_roles_in_same_project(): void
    {
        $this->expectException(\Illuminate\Database\QueryException::class);

        $user = User::factory()->create();
        $team = Team::factory()->create();
        $project = Project::factory()->create(['team_id' => $team->id]);
        $role1 = Role::factory()->create(['name' => 'member']);
        $role2 = Role::factory()->create(['name' => 'admin']);

        $user->projects()->attach($project->id, [
            'team_id' => $team->id,
            'role_id' => $role1->id,
        ]);

        // Attempt to attach a second role for the same user and project
        $user->projects()->attach($project->id, [
            'team_id' => $team->id,
            'role_id' => $role2->id,
        ]);
    }
}
