<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\{ProjectDispute, Task};
use App\Policies\{ProjectDisputePolicy, TaskPolicy};

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
    }
}
