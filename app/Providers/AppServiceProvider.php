<?php

namespace App\Providers;

use App\Models\Team;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\{ProjectDispute, Task, Objective, Project};
use App\Policies\{ProjectDisputePolicy, TaskPolicy, ObjectivePolicy, ProjectPolicy, TeamPolicy};

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(ProjectDispute::class, ProjectDisputePolicy::class);
        Gate::policy(Task::class, TaskPolicy::class);
        Gate::policy(Objective::class, ObjectivePolicy::class);
        Gate::policy(Project::class, ProjectPolicy::class);
        Gate::policy(Team::class, TeamPolicy::class);
    }
}
