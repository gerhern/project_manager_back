<?php

namespace Tests\Feature;

use App\Enums\{ProjectStatus, ObjectiveStatus, TaskStatus, TeamStatus};
use App\Models\{Project, Task, Objective, Team, User};
use App\Traits\SetTestingData;
use Carbon\Carbon;
use Database\Seeders\RolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class MiddlewareCasesTest extends TestCase
{
    use RefreshDatabase, SetTestingData;

    /**
     * @dataProvider RestrictedResourceProvider
     */
    public function test_cannot_modify_resources_in_restricted_states(string $modelClass, string $routeName, $status, $verb, $jsonData) :void{
 
       [$user, $team] = $this->createProject();

        $resource = match($modelClass) {
            Team::class => $modelClass::factory()->create(['status' => $status]),

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

        $routeParams = match($modelClass) {
            Objective::class => [$resource->project, $resource],
            Task::class      => [$resource->objective->project, $resource->objective, $resource],
            default          => [$resource],
        };
        // dd($verb, $routeName, $routeParams, $jsonData);

        $response = $this->actingAs($user)->json(
            $verb, 
            route($routeName, $routeParams), 
            $verb === 'DELETE' ? [] : $jsonData
        );
        // dd($response->getContent());

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
            'Team is Inactive' => [
                Team::class,
                'teams.inactive',
                TeamStatus::Inactive,
                'DELETE',
                ['name' => 'Attempt Update']
            ],
            'Project is Canceled' => [
                Project::class,
                'projects.update',
                ProjectStatus::Canceled,
                'PUT',
                ['name' => 'Attempt Update']
            ],
            'Project is Completed' => [
                Project::class,
                'projects.update',
                ProjectStatus::Completed,
                'PATCH',
                ['name' => 'Attempt Update']
            ],
            'Project is CancelInProgress' => [
                Project::class,
                'projects.update',
                ProjectStatus::CancelInProgress,
                'PATCH',
                ['name' => 'Attempt Update']
            ],
            'Objective is Completed' => [
                Objective::class,
                'projects.objectives.update',
                ObjectiveStatus::Completed,
                'PUT',
                ['title' => 'Attempt Update']
            ],
            'Objective is Canceled' => [
                Objective::class,
                'projects.objectives.update',
                ObjectiveStatus::Canceled,
                'PATCH',
                ['title' => 'Attempt Update']
            ],
            'Task is Completed' => [
                Task::class,
                'projects.objectives.tasks.update',
                TaskStatus::Completed,
                'PUT',
                ['title' => 'Attempt Update', 'due_date' => Carbon::now()->addDays(2)->toDateTimeString()]
            ],
            'Task is Canceled' => [
                Task::class,
                'projects.objectives.tasks.update',
                TaskStatus::Canceled,
                'PUT',
                ['title' => 'Attempt Update', 'due_date' => Carbon::now()->addDays(2)->toDateTimeString()]
            ],
        ];
    }

    public function test_only_members_of_project_can_view_project(): void {

        $this->seed('RolesSeeder');

        [$user, $team, $project] = $this->createProject();
        $user->assignRole('User');
        $admin = User::factory()->create()->assignRole('Admin');
        $stranger = User::factory()->create()->assignRole('User');
        $member = User::factory()->create()->assignRole('Member');

        $this->addUserToProject($project, $user, 'User');
        $this->addUserToTeam($team, $admin, 'Admin');
        $this->addUserToTeam($team, $member, 'Member');

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

        [$user, $teamA] = $this->createTeam();

        [, $teamB] = $this->createTeam(['status' => TeamStatus::Inactive]);

        $this->addUserToTeam($teamA, $user);
        $this->addUserToTeam($teamB, $user);

        $response = $this->actingAs($user)
            ->putJson(route('teams.update', $teamA), ['name' => 'newName']);
            $response->assertStatus(200);

        $this->actingAs($user)
            ->putJson(route('teams.update', $teamB), ['name' => 'newName'])
            ->assertStatus(403);
    }
    
}
