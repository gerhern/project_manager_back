<?php

namespace App\Observers;

use App\enums\ObjectiveStatus;
use App\Enums\ProjectStatus;
use App\Models\Project;

class ProjectObserver
{
    public function updated(Project $project): void {
        if($project->wasChanged('status')){
            if($project->status === ProjectStatus::Canceled){
                $project->objectives()
                    ->whereNotIn('status', [ObjectiveStatus::Canceled, ObjectiveStatus::Completed])
                    ->update(['status' =>  ObjectiveStatus::Canceled->name]);
            }
        }
    }
}
