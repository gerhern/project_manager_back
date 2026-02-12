<?php

namespace Tests\Feature;

use App\Enums\DisputeStatus;
use App\Enums\ProjectStatus;
use App\Traits\SetTestingData;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CommandTest extends TestCase
{
    use RefreshDatabase, SetTestingData;

    public function test_command_resolve_disputes_over_15_days(): void {
        [$ownerA, $teamA, $projectA] = $this->createProject();
        [$ownerB, $teamB, $projectB] = $this->createProject();

        $date = Carbon::now();

        $this->createDispute($projectA, $ownerB, DisputeStatus::Open, $date);
        $this->createDispute($projectB, $ownerA, DisputeStatus::Open, $date->subDays(2));

        $this->artisan('dispute:resolve');

        $this->assertDatabaseHas('projects', ['id' => $projectB->id, 'status' => ProjectStatus::Canceled]);
        $this->assertDatabaseHas('project_disputes', ['project_id' => $projectB->id, 'status' => DisputeStatus::Expired]);

        $this->assertDatabaseHas('projects', ['id' => $projectA->id, 'status' => ProjectStatus::Active]);
        $this->assertDatabaseHas('project_disputes', ['project_id' => $projectA->id, 'status' => DisputeStatus::Open]);
    }
}
