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

        $roles = Role::all();

        $admin = User::factory()->create(['name' => 'Carmine', 'email' => '1@1.com']);
        $allUsers = User::factory()->count(9)->create()->push($admin);

        $specialCount = 0;

        Team::factory()->count(5)->create([
            // 'user_id' => fn() => $allUsers->random()->id
        ])->each(function ($team) use (&$specialCount, $allUsers, $roles) {

            $owner = $allUsers->random();
            $team->members()->attach($owner->id, 
                ['role_id' => $roles->where('name', 'Owner')->first()->id]);

            $otherMembers = $allUsers->where('id', '!=', $owner->id)->random(3);
            foreach ($otherMembers as $user) {
                $team->members()->attach($user->id, [
                    'role_id' => collect([$roles->where('name', 'Admin')->first()->id, $roles->where('name', 'Member')->first()->id])->random()
                ]);
            }

            // 1. Determinar Estatus del Proyecto
            $projectStatus = ProjectStatus::Active->name;
            if (rand(0, 1) && $specialCount < 4) {
                $projectStatus = ($specialCount < 2) ? ProjectStatus::Completed->name : ProjectStatus::Canceled;
                $specialCount++;
            }

            $project = Project::factory()->create([
                'team_id' => $team->id,
                'user_id' => $allUsers->random()->id,
                'status' => $projectStatus
            ]);

            if ($projectStatus === ProjectStatus::Canceled || $projectStatus === ProjectStatus::CancelInProgress) {
                // 1. Decidir aleatoriamente si creamos disputa
                if (rand(0, 1)) {
                    $status = collect([
                        DisputeStatus::Expired,
                        DisputeStatus::Accepted,
                    ])->random();
                    
                    $disputeStatus = $projectStatus === ProjectStatus::CancelInProgress ? DisputeStatus::Open : $status;
                        
                    ProjectDispute::factory()->create([
                            'project_id' => $project->id,
                            'user_id' => $allUsers->random()->id,
                            'status' => $disputeStatus->name,
                            'expired_at' => ($disputeStatus === 'expired') ? now()->subDays(5) : now()->addDays(5),
                        ]);
                }
            }

            $team->members->each(function ($user) use ($project, $roles) {
                $project->users()->attach($user->id, [
                    'role_id' => collect([
                        $roles->where('name', 'Manager')->first()->id,
                        $roles->where('name', 'User')->first()->id,
                        $roles->where('name', 'Viewer')->first()->id
                    ])
                ->random()
                ]);
            });

            // 2. Crear Objetivos
            Objective::factory()->count(3)->create([
                'project_id' => $project->id,
                'status' => $this->getStatusFromParent($projectStatus, [
                    ObjectiveStatus::NotCompleted->name,
                    ObjectiveStatus::Completed->name,
                    ObjectiveStatus::Canceled->name
                ])
            ])->each(function ($objective) use ($projectStatus) {

                // 3. Crear Tareas
                Task::factory()->count(5)->create([
                    'objective_id' => $objective->id,
                    'status' => $this->getStatusFromParent($objective->status->name, [
                        TaskStatus::Assigned->name,
                        TaskStatus::Canceled->name,
                        TaskStatus::Completed->name,
                        TaskStatus::InProgress->name,
                        TaskStatus::Pending->name
                    ])
                ]);
            });
        });

        $disputedProjects = Project::where('status', ProjectStatus::CancelInProgress)->get();
        foreach ($disputedProjects as $disputed) {
            $this->createDispute($disputed, $allUsers->random());
        };
    }

    // private function getRandomStatus(string $parentStatus, array $allOptions): string
    // {

    //     if (in_array($parentStatus, ['Completed', 'Cancelled'])) {
    //         return collect(['Completed', 'Cancelled'])->intersect($allOptions)->random() 
    //             ?? $parentStatus;
    //     }

    //     // Si el padre está activo, el hijo puede ser cualquier cosa
    //     return collect($allOptions)->random();
    // }


    private function getStatusFromParent(mixed $parentStatus, array $availableOptions): mixed
    {
        // Definimos qué estados se consideran "Finalizados"
        $closedStatuses = [
            ProjectStatus::Completed,
            ProjectStatus::Canceled,
            ObjectiveStatus::Completed,
            ObjectiveStatus::Canceled
        ];

        if (in_array($parentStatus, $closedStatuses)) {
            // Si el padre está cerrado, filtramos las opciones del hijo que también representen cierre
            return collect($availableOptions)
                ->filter(
                    fn($status) => str_contains(strtolower($status), 'complete')
                    || str_contains(strtolower($status), 'canceled')
                    // || str_contains(strtolower($status), 'done')
                )
                ->random();
        }

        return collect($availableOptions)->random();
    }
    public function createDispute(Project $project, User $user, $status = DisputeStatus::Open, $date = null): ProjectDispute
    {
        $date ??= Carbon::now()->addDays(15);
        return ProjectDispute::create([
            'project_id' => $project->id,
            'user_id' => $user->id,
            'expired_at' => $date,
            'status' => $status->name
        ]);
    }
}
