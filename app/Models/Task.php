<?php

namespace App\Models;

use App\Enums\TaskStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    /** @use HasFactory<\Database\Factories\TaskFactory> */
    use HasFactory;

    protected $guarded = []; // Habilita asignación masiva

    protected $casts = [
        'status' => TaskStatus::class, // <--- La magia
    ];

    // Relación con Periodo
    public function period()
    {
        return $this->belongsTo(Period::class);
    }

    // Relación con Logs de tiempo
    public function timeLogs()
    {
        return $this->hasMany(TaskTimeLog::class);
    }

    public function getTotalSpentAttribute()
    {
        return $this->timeLogs->sum('minutes_spent');
    }

    // Relación con Subtareas
    public function subtasks()
    {
        return $this->hasMany(Subtask::class);
    }

    // Tiempo total estimado de subtareas
    public function getSubtasksTotalEstimatedAttribute()
    {
        return $this->subtasks->sum('estimated_minutes');
    }

    // Tiempo total invertido en subtareas
    public function getSubtasksTotalSpentAttribute()
    {
        return $this->subtasks->sum('spent_minutes');
    }

    // Tiempo estimado efectivo: solo el tiempo de la tarea
    // Las subtareas NO expanden el tiempo total, solo completan el tiempo estimado
    public function getEffectiveEstimatedMinutesAttribute()
    {
        return $this->estimated_minutes;
    }

    // Tiempo invertido efectivo: suma de TaskTimeLog + tiempo de subtareas
    // Las subtareas siempre contribuyen al "trabajo realizado"
    public function getEffectiveSpentMinutesAttribute()
    {
        if ($this->completion_method === 'subtasks') {
            // Para "Por Subtareas": solo tiempo de subtareas
            return $this->subtasks_total_spent;
        }

        // Para "Por Tiempo": TaskTimeLog + tiempo de subtareas
        return $this->total_spent + $this->subtasks_total_spent;
    }

    // Cálculo de progreso híbrido
    public function getProgressAttribute()
    {
        if ($this->completion_method === 'subtasks') {
            $totalSubtasks = $this->subtasks->count();

            if ($totalSubtasks === 0) {
                return 0;
            }

            // Factor 1: Porcentaje de subtareas completadas (50% peso)
            $completedSubtasks = $this->subtasks->where('is_completed', true)->count();
            $completionRatio = $completedSubtasks / $totalSubtasks;

            // Factor 2: Porcentaje de tiempo invertido (50% peso)
            $subtasksEstimated = $this->subtasks_total_estimated;
            $subtasksSpent = $this->subtasks_total_spent;

            // Si hay tiempo estimado en subtareas, usar cálculo híbrido
            if ($subtasksEstimated > 0) {
                $effectiveEstimated = max($this->estimated_minutes, $subtasksEstimated);
                $timeRatio = min($subtasksSpent / $effectiveEstimated, 1); // Cap at 100%

                // Fórmula híbrida: 50% completitud + 50% tiempo
                return round(($completionRatio * 0.5 + $timeRatio * 0.5) * 100);
            }

            // Sin tiempo en subtareas, solo contar por completitud
            return round($completionRatio * 100);
        }

        // Tareas "Por Tiempo" - usar TaskTimeLog
        if (! $this->estimated_minutes || $this->estimated_minutes == 0) {
            return 0;
        }

        return round(($this->total_spent / $this->estimated_minutes) * 100);
    }
}
