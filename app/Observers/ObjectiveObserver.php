<?php

namespace App\Observers;

use App\enums\ObjectiveStatus;
use App\enums\TaskStatus;
use App\Models\Objective;
use App\Models\Task;

class ObjectiveObserver
{
    public function updated(Objective $objective){
        if($objective->wasChanged('status')){
            if($objective->status === ObjectiveStatus::Canceled){
                $objective->tasks()
                ->whereNotIn('status', [TaskStatus::Canceled->name, TaskStatus::Completed->name])
                ->update(['status' => TaskStatus::Canceled->name]);
            }
        }
    }
}
