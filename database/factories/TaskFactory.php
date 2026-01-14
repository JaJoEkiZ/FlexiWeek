<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Task;
use App\Enums\TaskStatus;

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
        return [
            'title' => fake()->sentence(4), // Ej: "Revisar logs del servidor de producción"
            'estimated_minutes' => fake()->numberBetween(60, 480), // Entre 1h y 8hs
            'status' => fake()->randomElement(TaskStatus::cases()), // Elige un estado del Enum
            // 'period_id' se inyectará automáticamente            
        ];
    }
}
