<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subtask extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'description', 'is_completed', 'task_id', 'estimated_minutes', 'spent_minutes'];

    protected $casts = [
        'is_completed' => 'boolean',
        'estimated_minutes' => 'integer',
        'spent_minutes' => 'integer',
    ];

    public function task()
    {
        return $this->belongsTo(Task::class);
    }
}
