<?php

namespace Tests\Feature;

use App\enums\ObjectiveStatus;
use App\enums\TaskStatus;
use App\Models\Task;
use App\Traits\SetTestingData;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ObserverTest extends TestCase
{
    use RefreshDatabase, SetTestingData;
    public function test_cancelled_objective_cancel_his_own_task(): void
    {
        [$user, $team, $project, $objective, $taskA] = $this->createTask();
        [, , , , $taskB] = $this->createTask(['status' => TaskStatus::Canceled->name], $user, $team, $project, $objective);
        [, , , , $taskC] = $this->createTask(['status' => TaskStatus::Completed->name], $user, $team, $project, $objective);
        [, , , , $taskD] = $this->createTask(['status' => TaskStatus::Assigned->name], $user, $team, $project, $objective);
        [, , , , $taskE] = $this->createTask(['status' => TaskStatus::InProgress->name], $user, $team, $project, $objective);

        $objective->update(['status' => ObjectiveStatus::Canceled->name]);

        $this->assertEquals(
            4,
            Task::where('objective_id', $objective->id)
                ->where('status', TaskStatus::Canceled->name)
                ->count()
        );

        $this->assertEquals(
            1,
            Task::where('objective_id', $objective->id)
                ->where('status', TaskStatus::Completed->name)
                ->count()
        );
    }
}
