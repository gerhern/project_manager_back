<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Spatie\Permission\Models\Role;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        //roles
        Role::create(['name' => 'Admin']);
        Role::create(['name' => 'Manager']);
        Role::create(['name' => 'Contributor']);
        Role::create(['name' => 'Viewer']);

        //Project Statuses
        \DB::table('project_statuses')
            ->insert([
                ['name' => 'Active', 'code' => 'AC', 'description' => 'The project is currently active and ongoing.'],
                ['name' => 'Cancel in Progress', 'code' => 'CP', 'description' => 'The project is in the process of being canceled.'],
                ['name' => 'Canceled', 'code' => 'CA', 'description' => 'The project has been canceled and is no longer active.'],
                ['name' => 'Completed', 'code' => 'CO', 'description' => 'The project has been completed successfully.']
            ]);
        
        //Objective Statuses
        \DB::table('objective_statuses')
            ->insert([
                ['name' => 'Completed', 'code' => 'CO', 'description' => 'The objective has been completed successfully.'],
                ['name' => 'Not Completed', 'code' => 'NC', 'description' => 'The objective has not been completed yet.'],
                ['name' => 'Canceled', 'code' => 'CA', 'description' => 'The objective has been canceled and will not be completed.']
            ]);

        //Task statuses
        \DB::table('task_statuses')
            ->insert([
                ['name' => 'Pending', 'code' => 'PE', 'description' => 'The task is pending and has not been started yet.'],
                ['name' => 'Assigned', 'code' => 'AS', 'description' => 'The task has been assigned to a team member but work has not yet begun.'],
                ['name' => 'In Progress', 'code' => 'IP', 'description' => 'The task is currently in progress.'],
                ['name' => 'Completed', 'code' => 'CO', 'description' => 'The task has been completed successfully.'],
                ['name' => 'Canceled', 'code' => 'CA', 'description' => 'The task has been canceled and will not be completed.']
            ]);
    }
}
