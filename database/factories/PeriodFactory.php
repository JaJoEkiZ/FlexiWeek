<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Period>
 */
class PeriodFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Genera una fecha de inicio aleatoria en los últimos 2 meses
        $startDate = fake()->dateTimeBetween('-2 months', 'now');
        
        // Clona la fecha y le suma 6 días (semana típica)
        $endDate = (clone $startDate)->modify('+6 days');

        return [
            'name' => 'Semana del ' . $startDate->format('d/m'),
            'start_date' => $startDate,
            'end_date' => $endDate,
            // 'user_id' se inyectará desde el Seeder principal
        ];
    }
}
