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

}
