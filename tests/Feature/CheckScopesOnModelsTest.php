<?php

namespace Tests\Feature;

use App\Enums\DisputeStatus;
use App\Enums\ProjectStatus;
use App\Models\Project;
use App\Models\ProjectDispute;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckScopesOnModelsTest extends TestCase
{
    use RefreshDatabase;

    public function test_scope_argue_find_cases(): void
    {
        $team = Team::factory()->create();
        $user = User::factory()->create();
        $activeProject = Project::factory()->create(['status' => ProjectStatus::Active, 'team_id' => $team->id, 'user_id' => $user->id]);
        $cancelledProject = Project::factory()->create(['status' => ProjectStatus::Canceled, 'team_id' => $team->id, 'user_id' => $user->id]);
        $targetProject = Project::factory()->create(['status' => ProjectStatus::Completed, 'team_id' => $team->id, 'user_id' => $user->id]);

        $results = Project::Completed()->get();

        $this->assertTrue($results->contains($targetProject));
        $this->assertFalse($results->contains($activeProject));
        $this->assertFalse($results->contains($cancelledProject));

        $results->each(function ($project) {
            $this->assertEquals(ProjectStatus::Completed, $project->status);
        });

    }

    public function test_scope_disput_table_has_data(): void
    {
        $team = Team::factory()->create();
        $user = User::factory()->create();
        $projectWithDispute = Project::factory()->create(['status' => ProjectStatus::CancelInProgress, 'team_id' => $team->id, 'user_id' => $user->id]);
        $projectWithClosedDispute = Project::factory()->create(['status' => ProjectStatus::Active, 'team_id' => $team->id, 'user_id' => $user->id]);
        $projectWithoutDispute = Project::factory()->create(['status' => ProjectStatus::Active, 'team_id' => $team->id, 'user_id' => $user->id]);

        // Disputa Abierta
        ProjectDispute::factory()->create([
            'project_id' => $projectWithDispute->id,
            'user_id' => $projectWithDispute->user_id,
            'status' => DisputeStatus::Open,
            'expired_at' => now()->addDays(1),
        ]);

        // Disputa Cerrada
        ProjectDispute::factory()->create([
            'project_id' => $projectWithClosedDispute->id,
            'user_id' => $projectWithClosedDispute->user_id,
            'status' => DisputeStatus::Rejected,
            'expired_at' => now()->subDays(1),
        ]);

        // 2. ACT & ASSERT

        // Caso 1: Tiene disputa abierta (Debe ser true)
        $this->assertTrue($projectWithDispute->hasOpenDispute(), 'Falló: El proyecto debería reconocer la disputa abierta.');

        // Caso 2: Tiene disputa pero está cerrada (Debe ser false)
        $this->assertFalse($projectWithClosedDispute->hasOpenDispute(), 'Falló: El proyecto no debería reconocer una disputa cerrada como abierta.');

        // Caso 3: No tiene registros en la tabla (Debe ser false)
        $this->assertFalse($projectWithoutDispute->hasOpenDispute(), 'Falló: El proyecto sin registros reporta una disputa inexistente.');
    }
}
