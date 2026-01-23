<?php

namespace Tests\Feature;

use App\enums\ObjectiveStatus;
use App\Enums\ProjectStatus;
use App\enums\TaskStatus;
use App\Models\Objective;
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

    public function test_cancelled_project_cancel_his_own_objectives(): void {
        [$user, $team, $project, $objective] = $this->createObjective();
        $this->createObjective(['status' => ObjectiveStatus::Canceled->name], $user, $team, $project);
        $this->createObjective(['status' => ObjectiveStatus::Completed->name], $user,  $team, $project);
        $this->createObjective(['status' => ObjectiveStatus::NotCompleted->name], $user, $team, $project);

        $project->update(['status' => ProjectStatus::Canceled->name]);

        $this->assertEquals(
            3,
             Objective::where('project_id', $project->id)
             ->where('status', ObjectiveStatus::Canceled->name)
             ->count()
        );

        $this->assertEquals(1,
        Objective::where('project_id', $project->id)
        ->where('status', ObjectiveStatus::Completed->name)
        ->count());
    }

    public function test_project_cancel_objective_and_objective_cancel_task_in_a_row():void {
        [$user, $team, $project, $objectiveA,] = $this->createTask();
        $this->createTask([], $user, $team, $project, $objectiveA);

        [,,,$objectiveB] = $this->createObjective([], $user, $team, $project);
        $this->createTask([], $user, $team, $project, $objectiveB);
        $this->createTask([], $user, $team, $project, $objectiveB);

        [$user,$team, $projectB, $objective] = $this->createObjective([], $user, $team);
        $this->createTask([], $user, $team, $projectB, $objective);
        $this->createTask([], $user, $team, $projectB, $objective);

        $project->update(['status' => ProjectStatus::Canceled]);

        $this->assertEquals(4, Task::where('status', TaskStatus::Canceled)->count());
        $this->assertEquals(2, Objective::where('status', ObjectiveStatus::Canceled)->count());

        
        $this->assertEquals(2, Task::where('status', TaskStatus::Pending)->count());
        $this->assertEquals(1, Objective::where('status', ObjectiveStatus::NotCompleted)->count());

    }
}
