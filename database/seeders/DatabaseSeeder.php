<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Period;
use App\Models\Task;       // <--- No te olvides de importar esto
use App\Models\TaskTimeLog; // <--- Y esto
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Guardamos el usuario en la variable $user
        $user = User::factory()->create([
            'name' => 'Admin Cronograma',
            'email' => 'admin@cronograma.com',
            'password' => bcrypt('1234'),
        ]);

        // 2. Creamos 10 periodos vinculados explícitamente a ESE usuario
        // Usamos ->for($user) para hacer la relación
        $periods = Period::factory()
            ->count(10)
            ->for($user) 
            ->create();

        // 3. (Recomendado) Llenamos esos periodos con Tareas
        // Si no hacemos esto, vas a tener semanas vacías en el panel
        foreach ($periods as $period) {
            Task::factory()
                ->count(5) // 5 tareas por semana
                ->for($period) // Vinculadas a ESTE periodo
                ->create();
        }
    }
}