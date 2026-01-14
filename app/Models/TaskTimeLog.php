<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskTimeLog extends Model
{
    /** @use HasFactory<\Database\Factories\TaskTimeLogFactory> */
    use HasFactory;

    protected $fillable = ['task_id', 'minutes_spent', 'log_date'];
}
