<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subtask extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'description', 'is_completed', 'task_id', 'estimated_minutes', 'spent_minutes'];

    protected $casts = [
        'is_completed' => 'integer',
        'estimated_minutes' => 'decimal:2',
        'spent_minutes' => 'decimal:2',
    ];

    public function task()
    {
        return $this->belongsTo(Task::class);
    }
}
