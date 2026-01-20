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

    // subTareas
    public function getProgressAttribute()
    {
        if ($this->completion_method === 'subtasks') {
            $totalSubtasks = $this->subtasks->count();
            if ($totalSubtasks === 0) {
                return 0;
            }

            $completedSubtasks = $this->subtasks->where('is_completed', true)->count();

            return round(($completedSubtasks / $totalSubtasks) * 100);
        }

        if (! $this->estimated_minutes || $this->estimated_minutes == 0) {
            return 0;
        }

        return round(($this->total_spent / $this->estimated_minutes) * 100);
    }
}
