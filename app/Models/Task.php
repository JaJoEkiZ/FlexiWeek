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
        'status' => TaskStatus::class,
        'is_persistent' => 'boolean',
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

    // Tiempo estimado efectivo: tarea + subtareas con tiempo estimado
    // Subtareas con estimated_minutes = 0 se consideran incluidas en el tiempo de la tarea
    public function getEffectiveEstimatedMinutesAttribute()
    {
        return $this->estimated_minutes + $this->subtasks_total_estimated;
    }

    // Tiempo invertido efectivo: suma de TaskTimeLog + tiempo de subtareas
    // Las subtareas siempre contribuyen al "trabajo realizado", y el tiempo principal también.
    public function getEffectiveSpentMinutesAttribute()
    {
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
            $completedSubtasks = $this->subtasks->where('is_completed', 1)->count();
            $completionRatio = $completedSubtasks / $totalSubtasks;

            // Factor 2: Porcentaje de tiempo invertido (50% peso)
            $subtasksEstimated = $this->effective_estimated_minutes;
            $subtasksSpent = $this->effective_spent_minutes;

            // Si hay tiempo estimado, usar cálculo híbrido
            if ($subtasksEstimated > 0) {
                // effective_estimated_minutes ya es max(estimated_minutes, subtasks_total_estimated) porque los suma (revisar: no usa max, lo suma)
                $timeRatio = min($subtasksSpent / $subtasksEstimated, 1); // Cap at 100%

                // Fórmula híbrida: 50% completitud + 50% tiempo
                return round(($completionRatio * 0.5 + $timeRatio * 0.5) * 100);
            }

            // Sin tiempo en subtareas, solo contar por completitud
            return round($completionRatio * 100);
        }

        // Tareas "Por Tiempo" - usar tiempo efectivo (tarea + subtareas)
        $effectiveEstimated = $this->effective_estimated_minutes;
        if (! $effectiveEstimated || $effectiveEstimated == 0) {
            return 0;
        }

        return round(($this->effective_spent_minutes / $effectiveEstimated) * 100);
    }

    // Scope para tareas persistentes del usuario
    public function scopePersistent($query, $userId)
    {
        return $query->where('is_persistent', true)
            ->whereHas('period', fn ($q) => $q->where('user_id', $userId));
    }
}
