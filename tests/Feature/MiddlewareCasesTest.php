<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

use App\Models\Team;
use App\Models\User;
use App\Models\Project;
use App\Enums\ProjectStatus;

class MiddlewareCasesTest extends TestCase
{

    use RefreshDatabase;

    public function test_cannot_modify_project_in_restricted_states(): void
    {
        $team = Team::factory()->create();
        $user = User::factory()->create();
        $restrictedStatuses = [
            ProjectStatus::Completed,
            ProjectStatus::Canceled,
            ProjectStatus::CancelInProgress
        ];

        foreach ($restrictedStatuses as $status) {
            $project = Project::factory()->create([
                'team_id' => $team->id,
                'user_id' => $user->id,
                'status' => $status
            ]);

            // Intentamos una operación de actualización (PUT)
            $response = $this->actingAs($user)
                            ->putJson("/api/projects/{$project}", [
                                'name' => 'Nuevo Nombre'
                            ]);

            // Assert: 403 Forbidden o 422 Unprocessable Entity
            $response->assertStatus(403);
            
            // Opcional: Verificar que el mensaje de error sea descriptivo
            $response->assertJsonPath('message', 'Project status does not allow modifications.');
        }
    }

    // public function test_can_modify_active_project(): void
    // {
    //     $user = User::factory()->create();
    //     $project = Project::factory()->create([
    //         'user_id' => $user->id,
    //         'status' => ProjectStatus::Active
    //     ]);

    //     $response = $this->actingAs($user)
    //                     ->putJson("/api/projects/{$project->id}", [
    //                         'name' => 'Nombre Válido'
    //                     ]);

    //     $response->assertStatus(200);
    // }
}
