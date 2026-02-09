<?php

namespace Database\Seeders;

use App\Enums\ProjectStatus;
use App\Models\Project;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Project::factory(6)->create();
        Project::factory(2)->create(['status' => ProjectStatus::Completed]);
        Project::factory(2)->create(['status' => ProjectStatus::Canceled]);
    }
}
