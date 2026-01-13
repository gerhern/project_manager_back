<?php

namespace Tests\Feature;

use App\Enums\{ProjectStatus, ObjectiveStatus, TaskStatus};
use App\Models\{Project, Task, Objective, Team, User};
use Illuminate\Foundation\Testing\RefreshDatabase;
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
        ]);
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

    
}
