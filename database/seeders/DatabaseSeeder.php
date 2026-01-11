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
        $this->call([
            RoleSeeder::class,
            ProjectStatusesSeeder::class,
            ObjectiveStatusesSeeder::class,
            TaskStatusesSeeder::class
        ]);
        
    }
}
