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

    public function getProgressAttribute()
    {
        if (! $this->estimated_minutes || $this->estimated_minutes == 0) {
            return 0;
        }

        return round(($this->total_spent / $this->estimated_minutes) * 100);
    }
}
