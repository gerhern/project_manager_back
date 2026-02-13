<?php

namespace Tests\Feature;

use App\Enums\DisputeStatus;
use App\Enums\ProjectStatus;
use App\Models\Project;
use App\Models\ProjectDispute;
use App\Models\Team;
use App\Models\User;
use App\Traits\SetTestingData;
use Database\Seeders\RolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckScopesOnModelsTest extends TestCase
{
    use RefreshDatabase, SetTestingData;


    public function test_scope_argue_find_cases(): void
    {
        [$user, $team] = $this->createProject();
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
        [$user, $team] = $this->createTeam();
        $projectWithDispute = Project::factory()->create(['status' => ProjectStatus::CancelInProgress, 'team_id' => $team->id, 'user_id' => $user->id]);
        $projectWithClosedDispute = Project::factory()->create(['status' => ProjectStatus::Active, 'team_id' => $team->id, 'user_id' => $user->id]);
        $projectWithoutDispute = Project::factory()->create(['status' => ProjectStatus::Active, 'team_id' => $team->id, 'user_id' => $user->id]);

        ProjectDispute::factory()->create([
            'project_id' => $projectWithDispute->id,
            'user_id' => $projectWithDispute->user_id,
            'status' => DisputeStatus::Open,
            'expired_at' => now()->addDays(1),
        ]);

        ProjectDispute::factory()->create([
            'project_id' => $projectWithClosedDispute->id,
            'user_id' => $projectWithClosedDispute->user_id,
            'status' => DisputeStatus::Rejected,
            'expired_at' => now()->subDays(1),
        ]);

        $this->assertTrue($projectWithDispute->hasOpenDispute());
        $this->assertFalse($projectWithClosedDispute->hasOpenDispute());
        $this->assertFalse($projectWithoutDispute->hasOpenDispute());
    }
}
