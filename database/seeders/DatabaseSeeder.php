<?php

namespace Database\Seeders;

use App\Enums\DisputeStatus;
use App\Enums\ObjectiveStatus;
use App\Enums\ProjectStatus;
use App\Enums\TaskStatus;
use App\Models\Objective;
use App\Models\Project;
use App\Models\ProjectDispute;
use App\Models\Task;
use App\Models\Team;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Spatie\Permission\Models\Role;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([RolesSeeder::class]);
        $roles = Role::all()->keyBy('name');

        $admin = User::factory()->create(['name' => 'Carmine', 'email' => '1@1.com']);
        $allUsers = User::factory()->count(9)->create()->push($admin);

        Team::factory(5)->create()->each(function ($team) use ($allUsers, $roles) {
            $owner = $allUsers->random();

            $team->members()->attach($owner->id, ['role_id' => $roles['Owner']->id]);

            $otherMembers = $allUsers->where('id', '!=', $owner->id)->random(3);
            foreach ($otherMembers as $user) {
                $team->members()->attach($user->id, [
                    'role_id' => collect([$roles['Admin']->id, $roles['Member']->id])->random()
                ]);
            }

            $team->load('members');

            $projectStatus = $this->syncProjectStatus();

            $project = Project::factory()->create([
                'team_id' => $team->id,
                'user_id' => $owner->id,
                'status' => $projectStatus
            ]);

            $team->members->each(function ($user) use ($project, $roles, $owner) {
                $project->users()->attach($user->id, [
                    'role_id' => ($user->id === $owner->id)
                        ? $roles['Manager']->id
                        : collect([$roles['User']->id, $roles['Viewer']->id])->random()
                ]);
            });

            $project->load('users');

            if (in_array($projectStatus, [ProjectStatus::Canceled, ProjectStatus::CancelInProgress])) {
                if($projectStatus === ProjectStatus::CancelInProgress){

                    ProjectDispute::factory()->create([
                        'project_id' => $project->id,
                        'user_id' => $project->users->random()->id,
                        'status' => DisputeStatus::Open
                    ]);    
                }else{
                    if (rand(0, 1)) {
                        $disputeStatus = collect([DisputeStatus::Expired, DisputeStatus::Accepted])->random();
    
                        ProjectDispute::factory()->create([
                            'project_id' => $project->id,
                            'user_id' => $project->users->random()->id,
                            'status' => $disputeStatus,
                        ]);
                    }
                }
            }

            $objectiveStatus = $this->syncObjectiveStatus($projectStatus);

            Objective::factory(3)->create([
                'project_id' => $project->id,
                'status' => $objectiveStatus
            ])->each(function ($objective) use ($project, $objectiveStatus) {

                Task::factory(5)->create([
                    'objective_id' => $objective->id,
                ])->each(function ($task) use ($project, $objectiveStatus) {

                    $taskStatus = $this->syncTaskStatus($objectiveStatus);

                    $assignedUserId = ($taskStatus === TaskStatus::Pending)
                        ? null
                        : $project->users->random()->id;

                    $task->update([
                        'status' => $taskStatus,
                        'user_id' => $assignedUserId,
                    ]);
                });
            });
        });
    }

    private function syncProjectStatus(){
        return $this->weightedRandom([
            ProjectStatus::Active->name => 40,
            ProjectStatus::Canceled->name => 20,
            ProjectStatus::CancelInProgress->name => 20,
            ProjectStatus::Completed->name => 20
        ], ProjectStatus::class);
    }

    private function syncObjectiveStatus(ProjectStatus $projectStatus): ObjectiveStatus
    {
        return match ($projectStatus) {
            ProjectStatus::Completed,
            ProjectStatus::Canceled => collect([ObjectiveStatus::Completed, ObjectiveStatus::Canceled])->random(),

            default => $this->weightedRandom([
                ObjectiveStatus::NotCompleted->name => 50,
                ObjectiveStatus::Completed->name => 40,
                ObjectiveStatus::Canceled->name => 10,
            ], ObjectiveStatus::class),
        };
    }

    private function syncTaskStatus(ObjectiveStatus $objectiveStatus): TaskStatus
    {
        return match ($objectiveStatus) {
            ObjectiveStatus::Completed,
            ObjectiveStatus::Canceled => collect([TaskStatus::Completed, TaskStatus::Canceled])->random(),

            default => $this->weightedRandom([
                TaskStatus::InProgress->name => 30,
                TaskStatus::Assigned->name => 20,
                TaskStatus::Pending->name => 20,
                TaskStatus::Completed->name => 20,
                TaskStatus::Canceled->name => 10,
            ], TaskStatus::class),
        };
    }
    private function weightedRandom(array $weights, string $enumClass): mixed
    {
        $rand = rand(1, array_sum($weights));
        $current = 0;

        foreach ($weights as $value => $weight) {
            $current += $weight;
            if ($rand <= $current) {
                return constant("$enumClass::$value");
            }
        }

        return $enumClass::cases()[0];
    }
}
