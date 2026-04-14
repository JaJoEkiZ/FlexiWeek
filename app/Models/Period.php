<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Period extends Model
{
   
    use HasFactory;

    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Y esto para que el Periodo sepa que tiene tareas
    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    public function getAvailableMinutesAttribute()
    {
        $start = \Carbon\Carbon::parse($this->start_date)->startOfDay();
        $end = \Carbon\Carbon::parse($this->end_date)->startOfDay();
        $days = abs($start->diffInDays($end)) + 1;
        return $days * 14 * 60; // 14 horas útiles por día
    }

    public function getAssignedMinutesAttribute()
    {
        // Se asegura de excluir tareas canceladas, ya que esas no ocupan espacio en el límite realista
        $activeTasks = $this->tasks->where('status', '!=', \App\Enums\TaskStatus::Cancelled);
        return $activeTasks->sum('effective_estimated_minutes');
    }

}
