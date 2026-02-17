<?php

namespace Database\Factories;

use App\Enums\TaskStatus;
use App\Models\Objective;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Task>
 */
class TaskFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {

        $jobTitles = ['Implement', 'Fix', 'Refactor', 'Testing', 'Design'];
        $features = ['authentication', 'payment module', 'push notifications', 'user interface', 'API REST'];

        return [
            'title' => $this->faker->randomElement($jobTitles) . ' ' . $this->faker->randomElement($features),
            'description' => $this->faker->paragraph(2),
            'due_date' => $this->faker->dateTimeBetween('now', '+1 month'),
            'objective_id' => Objective::factory(),
            'user_id' => null, // Por defecto pendiente
            'status' => TaskStatus::Pending,
        ];
    }
}
