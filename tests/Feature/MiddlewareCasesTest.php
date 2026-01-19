<?php

namespace Tests\Feature;

use App\Enums\{ProjectStatus, ObjectiveStatus, TaskStatus, TeamStatus};
use App\Models\{Project, Task, Objective, Team, User};
use Database\Seeders\RolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class MiddlewareCasesTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @dataProvider RestrictedResourceProvider
     */
    public function test_cannot_modify_resources_in_restricted_states(string $modelClass, string $routeName, $status, $verb) :void{
        $team = Team::factory()->create();
        $user = User::factory()->create();

        $resource = match($modelClass) {
            Project::class => $modelClass::factory()->create([
                'team_id' => $team->id, 'user_id' => $user->id, 'status' => $status
            ]),

            Objective::class => $modelClass::factory()->create([
                'project_id' => Project::factory()->create(['team_id' => $team->id, 'user_id' => $user->id]),
                'status' => $status
            ]),

            Task::class => (function() use ($modelClass, $team, $user, $status) {
                $project = Project::factory()->create(['team_id' => $team->id, 'user_id' => $user->id]);
                $objective = Objective::factory()->create(['project_id' => $project->id]);
                return $modelClass::factory()->create(['objective_id' => $objective->id, 'status' => $status]);
            })(),
        };

        $response = $this->actingAs($user)->json(
            $verb, 
            route($routeName, $resource), 
            $verb === 'DELETE' ? [] : ['name' => 'Attempted Update']
        );

        $classNameModel = class_basename($modelClass);
        $response->assertStatus(403);
        $response->assertJson([
            'message' => "Can't modify resource; {$classNameModel} is {$resource->status->name}"
        ])
        ->assertJsonStructure(['success', 'message']);
    }

    public static function restrictedResourceProvider(): array
    {
        return [
            'Project is Canceled' => [
                Project::class,
                'project.update',
                ProjectStatus::Canceled,
                'PUT'
            ],
            'Project is Completed' => [
                Project::class,
                'project.update',
                ProjectStatus::Completed,
                'PATCH'
            ],
            'Project is CancelInProgress' => [
                Project::class,
                'project.update',
                ProjectStatus::CancelInProgress,
                'DELETE'
            ],
            'Objective is Completed' => [
                Objective::class,
                'objective.update',
                ObjectiveStatus::Completed,
                'PUT'
            ],
            'Objective is Canceled' => [
                Objective::class,
                'objective.update',
                ObjectiveStatus::Canceled,
                'PATCH'
            ],
            'Task is Completed' => [
                Task::class,
                'task.update',
                TaskStatus::Completed,
                'DELETE'
            ],
            'Task is Canceled' => [
                Task::class,
                'task.update',
                TaskStatus::Canceled,
                'PUT'
            ],
        ];
    }

    public function test_only_members_of_project_can_view_project(): void {

        $this->seed('RolesSeeder');

        $team = Team::factory()->create();
        $user = User::factory()->create()->assignRole('User');
        $admin = User::factory()->create()->assignRole('Admin');
        $stranger = User::factory()->create()->assignRole('User');
        $member = User::factory()->create()->assignRole('Member');

        $project = Project::factory()->create(['user_id' => $admin->id, 'team_id' => $team->id]);

        $user->projects()->attach($project->id, [
            'role_id' => Role::where('name',  'User')->first()->id
        ]);

        $admin->teams()->attach($team->id, [
            'role_id' => Role::where('name', 'Admin')->first()->id
        ]);
        $member->teams()->attach($team->id, [
            'role_id' => Role::where('name', 'Member')->first()->id
        ]);

        $this->actingAs($user)
            ->getJson(route('project.show', $project))
            ->assertJsonStructure(['success', 'message'])
            ->assertStatus(200  );

        $this->actingAs($admin)
            ->getJson(route('project.show', $project))
            ->assertJsonStructure(['success', 'message'])
            ->assertStatus(200  );

        $this->actingAs($member)
            ->getJson(route('project.show', $project))
            ->assertJsonStructure(['success', 'message'])
            ->assertStatus(403);

        $this->actingAs($stranger)
            ->getJson(route('project.show', $project))
            ->assertJsonStructure(['success', 'message'])
            ->assertStatus(403);
    }

    public function test_only_active_status_can_be_updated():void {
        $this->seed(RolesSeeder::class);

        $user = User::factory()->create()->assignRole('Admin');
        $roleId = Role::where('name', 'Admin')->first()->id;
        $teamA = Team::factory()->create(['status' => TeamStatus::Active]);
        $teamB = Team::factory()->create(['status' => TeamStatus::Inactive]);

        $user->teams()->attach($teamA->id, ['role_id' => $roleId]);
        $user->teams()->attach($teamB->id, ['role_id' => $roleId]);

        $response = $this->actingAs($user)
            ->putJson(route('teams.update', $teamA), ['name' => 'newName']);
            $response->assertStatus(200);

        $this->actingAs($user)
            ->putJson(route('teams.update', $teamB), ['name' => 'newName'])
            ->assertStatus(403);
    }
    
}
